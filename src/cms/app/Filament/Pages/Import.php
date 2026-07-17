<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Authorization\Permission;
use App\Events\StaticWebsite\BuildEvent;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Import\ImportFailedException;
use App\Import\ZipImporter;
use App\Rules\Virusscanner;
use App\Services\BuildContextService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Webmozart\Assert\Assert;

use function __;
use function app;

class Import extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'import';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.import';
    protected static ?string $navigationIcon = 'heroicon-o-document-plus';

    /** @var ?array<TemporaryUploadedFile> $files */
    public ?array $files;

    public static function canAccess(): bool
    {
        return Authorization::hasPermission(Permission::CORE_ENTITY_IMPORT);
    }

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::FUNCTIONAL_MANAGEMENT->value);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            FileUpload::make('files')
                ->required()
                ->label(__('import.files'))
                ->acceptedFileTypes([
                    'application/zip',
                    'application/x-zip-compressed',
                ])
                ->rules([
                    app()->get(Virusscanner::class),
                ])
                ->storeFiles(false)
                ->multiple(),
        ]);
    }

    public function submit(BuildContextService $buildContextService, ZipImporter $zipImporter): void
    {
        $form = $this->getForm('form');
        $formState = $form?->getState();
        Assert::isArray($formState);
        Assert::keyExists($formState, 'files');
        Assert::isArray($formState['files']);
        Assert::allIsInstanceOf($formState['files'], TemporaryUploadedFile::class);

        Log::info('Import started');
        $buildContextService->disableBuild();

        try {
            $zipImporter->importFiles($formState['files'], Authentication::user()->id, Authentication::organisation()->id->toString());
        } catch (ImportFailedException $exception) {
            Log::error('Import failed', ['message' => $exception->getMessage()]);

            Notification::make()
                ->title(__('import.failed'))
                ->icon('heroicon-o-document-plus')
                ->danger()
                ->send();
        }

        Log::info('Import success');

        $buildContextService->enableBuild();

        Log::debug('build event triggered by organisation observer');
        BuildEvent::dispatch();

        Notification::make()
            ->title(__('import.upload_success'))
            ->body(__('import.upload_success_body'))
            ->icon('heroicon-o-document-plus')
            ->success()
            ->send();

        $form->fill();
    }
}
