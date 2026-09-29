<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Components\Uuid\Uuid;
use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Jobs\TransferImportJob;
use App\Rules\Virusscanner;
use App\Transfer\Import\BundleReader;
use App\Transfer\Import\PreviewBuilder;
use App\Transfer\TransferBundleStorage;
use App\Transfer\TransferException;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Webmozart\Assert\Assert;

use function __;
use function app;
use function basename;
use function collect;
use function is_array;
use function is_string;
use function sprintf;

class TransferImport extends Page implements HasForms
{
    use InteractsWithForms;

    public const string DISK = TransferBundleStorage::DISK;
    public const string IMPORT_DIRECTORY = 'transfer/imports';

    protected static ?string $slug = 'transfer-import';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.transfer-import';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    /** @var ?array<TemporaryUploadedFile> $files */
    public ?array $files = null;

    // Server-authoritative: set only by analyse(). #[Locked] prevents the client from
    // tampering with the uploaded bundle path, which would otherwise allow importing
    // another organisation's export zip or traversing to arbitrary files on the disk.
    #[Locked]
    public ?string $bundlePath = null;

    #[Locked]
    public ?string $sourceOrganisation = null;

    #[Locked]
    public ?string $exportedAt = null;

    /** @var array<string, array<string, mixed>> $items */
    public array $items = [];

    public static function canAccess(): bool
    {
        return Authorization::hasPermission(Permission::TRANSFER_IMPORT);
    }

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::FUNCTIONAL_MANAGEMENT->value);
    }

    public static function getNavigationLabel(): string
    {
        return __('transfer.import_page_title');
    }

    public function getTitle(): string
    {
        return __('transfer.import_page_title');
    }

    // Resolved on demand rather than injected: Livewire components have no
    // constructor arguments, and public properties get serialized per request.
    private function bundleStorage(): TransferBundleStorage
    {
        return app(TransferBundleStorage::class);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('files')
                ->required()
                ->label(__('transfer.import_file'))
                ->acceptedFileTypes([
                    'application/zip',
                    'application/x-zip-compressed',
                ])
                ->rules([
                    app()->get(Virusscanner::class),
                ])
                ->storeFiles(false),
        ]);
    }

    public function analyse(BundleReader $bundleReader, PreviewBuilder $previewBuilder): void
    {
        $form = $this->getForm('form');
        $formState = $form?->getState();
        Assert::isArray($formState);
        $files = $formState['files'] ?? null;
        $file = is_array($files) ? collect($files)->first() : $files;
        Assert::isInstanceOf($file, TemporaryUploadedFile::class);

        $bundlePath = sprintf('%s/%s.zip', self::IMPORT_DIRECTORY, Uuid::generate()->toString());
        $this->bundleStorage()->putFile($bundlePath, $file->getRealPath());

        try {
            // Read the upload where it already sits locally; the copy on the disk
            // is only there for the queued job to pick up later.
            $bundle = $bundleReader->read($file->getRealPath());
        } catch (TransferException $exception) {
            $this->bundleStorage()->delete($bundlePath);

            Notification::make()
                ->title(__('transfer.import_invalid_file'))
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->bundlePath = $bundlePath;
        $this->sourceOrganisation = $bundle->sourceOrganisationName();
        $this->exportedAt = $bundle->exportedAt() === ''
            ? null
            : CarbonImmutable::parse($bundle->exportedAt())->format('d-m-Y H:i');
        $this->items = $previewBuilder->build($bundle, Authentication::organisation());

        $form->fill();
    }

    /**
     * Resolve the stored bundle path to a safe location inside the import directory.
     * Defence in depth on top of #[Locked]: rejects anything that is not a plain
     * upload filename in transfer/imports/, so no path traversal or foreign disk
     * location can be reached even if the property were somehow tampered with.
     */
    private function safeBundlePath(): ?string
    {
        if ($this->bundlePath === null) {
            return null;
        }

        $expected = sprintf('%s/%s', self::IMPORT_DIRECTORY, basename($this->bundlePath));

        return $this->bundlePath === $expected && Str::isUuid(basename($this->bundlePath, '.zip'))
            ? $expected
            : null;
    }

    public function import(): void
    {
        $bundlePath = $this->safeBundlePath();
        Assert::notNull($bundlePath);

        $plan = [];
        foreach ($this->items as $id => $item) {
            $strategy = $item['strategy'] ?? null;

            $plan[$id] = [
                'selected' => (bool) ($item['selected'] ?? false),
                'strategy' => is_string($strategy) ? $strategy : null,
            ];
        }

        TransferImportJob::dispatch(
            $bundlePath,
            $plan,
            Authentication::organisation()->id,
            Authentication::user()->id,
        );

        Notification::make()
            ->title(__('transfer.import_started'))
            ->icon('heroicon-o-archive-box')
            ->success()
            ->send();

        $this->reset('bundlePath', 'sourceOrganisation', 'exportedAt', 'items');
    }

    public function cancel(): void
    {
        $bundlePath = $this->safeBundlePath();

        if ($bundlePath !== null) {
            $this->bundleStorage()->delete($bundlePath);
        }

        $this->reset('bundlePath', 'sourceOrganisation', 'exportedAt', 'items');
    }

    /**
     * @return Collection<string, Collection<string, array<string, mixed>>>
     */
    public function groupedItems(): Collection
    {
        return collect($this->items)->groupBy('type_label', true);
    }
}
