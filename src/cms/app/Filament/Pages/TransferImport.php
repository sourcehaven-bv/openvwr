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
use App\Transfer\ConflictStrategy;
use App\Transfer\Import\BundleReader;
use App\Transfer\Import\ImportMatcher;
use App\Transfer\Import\TransferBundle;
use App\Transfer\TransferEntityType;
use App\Transfer\TransferException;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Webmozart\Assert\Assert;

use function __;
use function app;
use function collect;
use function fopen;
use function is_array;
use function is_string;
use function sprintf;

class TransferImport extends Page implements HasForms
{
    use InteractsWithForms;

    public const string DISK = 'filament';
    public const string IMPORT_DIRECTORY = 'transfer/imports';

    protected static ?string $slug = 'transfer-import';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.transfer-import';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    /** @var ?array<TemporaryUploadedFile> $files */
    public ?array $files = null;

    public ?string $bundlePath = null;
    public ?string $sourceOrganisation = null;
    public ?string $exportedAt = null;

    /** @var array<string, array<string, mixed>> $items */
    public array $items = [];

    public static function canAccess(): bool
    {
        return Authorization::hasPermission(Permission::CORE_ENTITY_IMPORT);
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

    public function form(Form $form): Form
    {
        return $form->schema([
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

    public function analyse(BundleReader $bundleReader, ImportMatcher $importMatcher): void
    {
        $form = $this->getForm('form');
        $formState = $form?->getState();
        Assert::isArray($formState);
        $files = $formState['files'] ?? null;
        $file = is_array($files) ? collect($files)->first() : $files;
        Assert::isInstanceOf($file, TemporaryUploadedFile::class);

        $disk = Storage::disk(self::DISK);
        $bundlePath = sprintf('%s/%s.zip', self::IMPORT_DIRECTORY, Uuid::generate()->toString());
        $stream = fopen($file->getRealPath(), 'rb');
        Assert::resource($stream);
        $disk->put($bundlePath, $stream);

        try {
            $bundle = $bundleReader->read($disk->path($bundlePath));
        } catch (TransferException $exception) {
            $disk->delete($bundlePath);

            Notification::make()
                ->title(__('transfer.import_invalid_file'))
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        $items = $this->buildItems($bundle, $importMatcher);

        $this->bundlePath = $bundlePath;
        $this->sourceOrganisation = $bundle->sourceOrganisationName();
        $this->exportedAt = $bundle->exportedAt() === ''
            ? null
            : CarbonImmutable::parse($bundle->exportedAt())->format('d-m-Y H:i');
        $this->items = $items;

        $form->fill();
    }

    /**
     * Build the selection rows shown in the preview: one per selectable entity,
     * annotated with whether the destination organisation already has it.
     *
     * @return array<string, array<string, mixed>>
     */
    private function buildItems(TransferBundle $bundle, ImportMatcher $importMatcher): array
    {
        $organisation = Authentication::organisation();
        $items = [];

        foreach ($bundle->entities as $id => $entity) {
            $typeValue = $entity['type'] ?? null;

            if (!is_string($typeValue)) {
                continue;
            }

            $type = TransferEntityType::from($typeValue);

            if ($type->isOwned() || $type->isLookup()) {
                continue;
            }

            $match = $importMatcher->match($type, $entity, $organisation);
            $name = $entity['name'] ?? null;

            $items[$id] = [
                'type_label' => $type->label(),
                'name' => is_string($name) ? $name : $id,
                'selected' => true,
                'has_match' => $match !== null,
                'match_name' => $match === null ? null : $type->displayName($match),
                'strategy' => $match === null ? null : ConflictStrategy::SKIP->value,
            ];
        }

        return $items;
    }

    public function import(): void
    {
        Assert::notNull($this->bundlePath);

        $plan = [];
        foreach ($this->items as $id => $item) {
            $strategy = $item['strategy'] ?? null;

            $plan[$id] = [
                'selected' => (bool) ($item['selected'] ?? false),
                'strategy' => is_string($strategy) ? $strategy : null,
            ];
        }

        TransferImportJob::dispatch(
            $this->bundlePath,
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
        if ($this->bundlePath !== null) {
            Storage::disk(self::DISK)->delete($this->bundlePath);
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
