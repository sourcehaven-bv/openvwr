<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Authorization\Permission;
use App\Enums\Import\ImportTarget;
use App\Events\StaticWebsite\BuildEvent;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Import\ImportFailedException;
use App\Import\Mapping\ArchiveInspector;
use App\Import\Mapping\DateFormatDetector;
use App\Import\Mapping\DryRunner;
use App\Import\Mapping\DryRunResult;
use App\Import\Mapping\EditableMapping;
use App\Import\Mapping\MappedRecordWriter;
use App\Import\Mapping\MappingAnalyser;
use App\Import\Mapping\MappingProfile;
use App\Import\Mapping\MappingProfileRepository;
use App\Import\Mapping\SheetReader;
use App\Import\Mapping\TargetOptions;
use App\Import\Mapping\TransformResolver;
use App\Import\Mapping\UnknownMappingTargetException;
use App\Import\ZipImporter;
use App\Rules\Virusscanner;
use App\Services\BuildContextService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Webmozart\Assert\Assert;

use function __;
use function abort;
use function abort_unless;
use function app;
use function array_key_first;
use function array_keys;
use function array_sum;
use function implode;
use function is_array;
use function is_string;
use function now;
use function sprintf;

/**
 * One import screen for two kinds of file.
 *
 * An OpenVWR export (zip) is inspected and handed to the existing importer. A
 * spreadsheet goes through analyse -> review -> dry-run -> apply, with the
 * mapping edited on screen. See docs/import_mapping_design.md.
 *
 * Everything the browser may not change is #[Locked]; the mapping itself and
 * the profile name are the only state the screen edits.
 */
class ImportMapping extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'import';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.import-mapping';
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    public const STEP_UPLOAD = 'upload';
    public const STEP_ARCHIVE = 'archive';
    public const STEP_REVIEW = 'review';
    public const STEP_RESULT = 'result';

    private const EMPTY_RESULT = [
        'fits' => 0,
        'issues' => [],
        'imported' => 0,
        'skipped' => 0,
        'failures' => [],
        'entities' => ['new' => [], 'fuzzy' => [], 'ambiguous' => [], 'unresolved' => []],
    ];

    #[Locked]
    public string $step = self::STEP_UPLOAD;

    /** @var ?array<TemporaryUploadedFile> $files */
    public ?array $files;

    public string $target = ImportTarget::DataBreachRecord->value;

    /** @var array<int, string> */
    #[Locked]
    public array $headers = [];

    /**
     * The rows are kept server-side under this key rather than in the
     * component: a sheet of a few thousand rows would otherwise travel with
     * every change to a dropdown.
     */
    #[Locked]
    public ?string $sheetKey = null;

    /**
     * Per source column: target and confidence, plus for yes/no columns feeding
     * a date field the date a "yes" stands for.
     *
     * @var array<string, array<string, string>>
     */
    public array $mapping = [];

    /**
     * What the dry-run found and, after apply, what was written.
     *
     * @var array{fits: int, issues: array<int, array{row: int, reason: string}>, imported: int, skipped: int, failures: array<int, array{row: int, reason: string}>, entities: array<string, array<string, mixed>>}
     */
    #[Locked]
    public array $result = self::EMPTY_RESULT;

    /** @var array<string, int> */
    #[Locked]
    public array $archiveContents = [];

    public ?string $profileName = null;

    #[Locked]
    public ?string $recognisedProfile = null;

    private ?EditableMapping $review = null;

    public function mount(): void
    {
        $this->files = null;
    }

    public static function canAccess(): bool
    {
        return Authorization::hasPermission(Permission::CORE_ENTITY_IMPORT);
    }

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::FUNCTIONAL_MANAGEMENT->value);
    }

    public function getTitle(): string
    {
        return __('import_mapping.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('target')
                ->label(__('import_mapping.target'))
                ->helperText(__('import_mapping.target_help'))
                ->options(ImportTarget::options())
                ->in(array_keys(ImportTarget::options()))
                ->required(),
            FileUpload::make('files')
                ->required()
                ->label(__('import_mapping.file_any'))
                ->acceptedFileTypes([
                    'text/csv',
                    'text/plain',
                    'application/csv',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/zip',
                    'application/x-zip-compressed',
                ])
                ->maxSize(Config::integer('import.mapping.max_upload_kb'))
                ->rules([app()->get(Virusscanner::class)])
                ->storeFiles(false)
                ->live()
                // Once a file is there the next step is never in doubt, so it
                // does not need a button of its own.
                ->afterStateUpdated(function (): void {
                    $this->analyse(
                        app(SheetReader::class),
                        app(MappingAnalyser::class),
                        app(MappingProfileRepository::class),
                    );
                }),
        ]);
    }

    /**
     * Step 1 -> 2: read the file and, for a spreadsheet, propose a mapping.
     */
    public function analyse(SheetReader $sheetReader, MappingAnalyser $analyser, MappingProfileRepository $repository): void
    {
        abort_unless(static::canAccess(), 403);

        $file = $this->validatedUpload();

        try {
            // The file itself says which route it belongs to, so the user does
            // not have to know the difference.
            if (Str::lower(File::extension($file->getClientOriginalName())) === 'zip') {
                $this->inspectArchive($file, app(ArchiveInspector::class));

                return;
            }

            $this->analyseSheet($file, $sheetReader, $analyser, $repository);
        } catch (ImportFailedException $exception) {
            $this->failed(__('import_mapping.read_failed'), $exception->getMessage());
        }
    }

    /**
     * Runs the field rules -- accepted types and the virus scan -- before the
     * file is opened, so nothing is parsed that has not passed them.
     */
    private function validatedUpload(): TemporaryUploadedFile
    {
        $state = $this->getForm('form')?->getState();
        Assert::isArray($state);

        // The field is required and accepts a single file, so validation
        // guarantees exactly one.
        $file = $state['files'] ?? null;
        Assert::isInstanceOf($file, TemporaryUploadedFile::class);

        return $file;
    }

    /**
     * Reads what the archive holds without importing, so the user can confirm.
     *
     * @throws ImportFailedException
     */
    private function inspectArchive(TemporaryUploadedFile $file, ArchiveInspector $inspector): void
    {
        $this->archiveContents = $inspector->inspect((string) $file->get());

        if ($this->archiveContents === []) {
            Notification::make()
                ->title(__('import_mapping.archive_empty'))
                ->warning()
                ->send();

            return;
        }

        $this->step = self::STEP_ARCHIVE;
    }

    /**
     * @throws ImportFailedException
     */
    private function analyseSheet(
        TemporaryUploadedFile $file,
        SheetReader $sheetReader,
        MappingAnalyser $analyser,
        MappingProfileRepository $repository,
    ): void {
        $sheet = $sheetReader->read($file->getClientOriginalName(), (string) $file->get());

        // The importer tolerates an empty sheet (a zip may hold one); a person
        // uploading one by hand is better told than shown an empty review.
        if ($sheet->rows === []) {
            throw new ImportFailedException(__('import_mapping.error.no_rows'));
        }

        $this->headers = $sheet->headers;
        $this->setRows($sheet->rows);

        $target = $this->importTarget();
        $modelClass = $target->modelClass();
        $saved = $repository->findByFingerprint(
            MappingProfile::fingerprint($this->headers),
            Authentication::organisation()->id,
        );
        $recognised = $saved !== null && $saved->target === $modelClass;

        $this->recognisedProfile = $recognised ? $saved->name : null;
        $this->mapping = EditableMapping::fromProfile(
            $this->headers,
            $recognised ? $saved->toMappingProfile() : $analyser->analyse($target, $this->headers, $sheet->rows),
        );
        $this->step = self::STEP_REVIEW;
    }

    /**
     * Hands the archive to the existing importer, which knows how to build the
     * registers it contains, snapshots and all.
     */
    public function applyArchive(BuildContextService $buildContextService, ZipImporter $zipImporter): void
    {
        abort_unless(static::canAccess(), 403);

        $file = $this->validatedUpload();

        $buildContextService->disableBuild();

        try {
            $zipImporter->importFiles([$file], Authentication::user()->id, Authentication::organisation()->id->toString());
        } catch (ImportFailedException $exception) {
            Log::warning('archive import failed', ['message' => $exception->getMessage()]);
            $this->failed(__('import.failed'), __('import_mapping.import_failed'));

            return;
        } finally {
            $buildContextService->enableBuild();
        }

        BuildEvent::dispatch();

        $this->step = self::STEP_RESULT;
        $this->result = ['imported' => array_sum($this->archiveContents)] + self::EMPTY_RESULT;

        Log::info('Import applied', ['route' => 'archive', 'registers' => $this->archiveContents]);

        Notification::make()
            ->title(__('import.upload_success'))
            ->body(__('import.upload_success_body'))
            ->success()
            ->send();
    }

    /**
     * Step 2 -> 3: check the mapping against every row, without saving.
     */
    public function dryRun(DryRunner $dryRunner): void
    {
        abort_unless(static::canAccess(), 403);

        $result = $this->runDry($dryRunner);

        if ($result === null) {
            return;
        }

        Notification::make()
            ->title(__('import_mapping.dry_run_done', [
                'fits' => $result->fitCount(),
                'issues' => $result->issueCount(),
            ]))
            ->info()
            ->send();
    }

    /**
     * Step 3: import the rows that fit, leaving the problem rows behind.
     */
    public function apply(DryRunner $dryRunner, MappedRecordWriter $writer, MappingProfileRepository $repository): void
    {
        abort_unless(static::canAccess(), 403);

        $result = $this->runDry($dryRunner);

        if ($result === null) {
            return;
        }

        $target = $this->importTarget();
        $profile = $this->review()->toProfile();
        $written = $writer->write($target, $profile, $result, Authentication::organisation()->id);

        $this->result = [
            'imported' => $written->imported,
            'skipped' => $written->skipped,
            'failures' => $written->failures,
            'entities' => $written->entityReport(),
        ] + $this->result;

        if (is_string($this->profileName) && $this->profileName !== '') {
            $repository->store(
                $this->profileName,
                $profile,
                $this->headers,
                Authentication::organisation()->id,
                Authentication::user()->id,
            );
        }

        Log::info('Import applied', [
            'route' => 'sheet',
            'target' => $target->value,
            'imported' => $written->imported,
            'skipped' => $written->skipped,
            'failed' => $written->failureCount(),
            'issues' => $result->issueCount(),
        ]);

        $this->step = self::STEP_RESULT;

        $clean = $written->failureCount() === 0;

        Notification::make()
            ->title($clean
                ? __('import_mapping.applied', ['count' => $written->imported])
                : __('import_mapping.applied_with_failures', [
                    'count' => $written->imported,
                    'failed' => $written->failureCount(),
                ]))
            ->status($clean ? 'success' : 'warning')
            ->send();
    }

    /**
     * The dry-run both steps start with. Null when the sheet is no longer
     * available; the user has been sent back to the start in that case.
     */
    private function runDry(DryRunner $dryRunner): ?DryRunResult
    {
        $rows = $this->review()->rows();

        if ($rows === []) {
            $this->restart();
            $this->failed(__('import_mapping.read_failed'), __('import_mapping.session_expired'));

            return null;
        }

        $problem = $this->mappingProblem();

        if ($problem !== null) {
            $this->failed(__('import_mapping.review_heading'), $problem);

            return null;
        }

        try {
            $profile = $this->review()->toProfile();
        } catch (UnknownMappingTargetException) {
            // The screen only offers allowed targets, so this did not come from
            // the screen.
            abort(403);
        }

        $result = $dryRunner->run($profile, $rows);

        $issues = [];
        foreach ($result->issues as $issue) {
            $issues[] = ['row' => $issue->rowNumber, 'reason' => $issue->reason];
        }

        $this->result = ['fits' => $result->fitCount(), 'issues' => $issues] + self::EMPTY_RESULT;

        return $result;
    }

    /**
     * What still has to be settled before the mapping can be run, as a message
     * for the user; null when nothing is in the way.
     */
    private function mappingProblem(): ?string
    {
        $undecided = $this->review()->headersNeedingDateFormat();

        if ($undecided !== []) {
            return __('import_mapping.date_format_missing', ['column' => $undecided[0]]);
        }

        $duplicates = $this->review()->duplicateTargets();

        if ($duplicates !== []) {
            $target = array_key_first($duplicates);

            return __('import_mapping.duplicate_target', [
                'field' => $this->review()->options()->label($target),
                'columns' => implode('", "', $duplicates[$target]),
            ]);
        }

        return null;
    }

    public function restart(): void
    {
        if ($this->sheetKey !== null) {
            Cache::forget($this->sheetKey);
        }

        $this->step = self::STEP_UPLOAD;
        $this->headers = [];
        $this->sheetKey = null;
        $this->review = null;
        $this->mapping = [];
        $this->result = self::EMPTY_RESULT;
        $this->recognisedProfile = null;
        $this->archiveContents = [];
        $this->profileName = null;
        $this->files = null;

        // Refill rather than clear: $target is required, so it must keep a value.
        $this->getForm('form')?->fill(['target' => $this->target]);
    }

    /**
     * The mapping as the review screen works with it.
     */
    public function review(): EditableMapping
    {
        $target = $this->importTarget();

        return $this->review ??= new EditableMapping(
            $target,
            $this->headers,
            $this->mapping,
            $this->storedRows(),
            $this->recognisedProfile !== null,
            new TargetOptions($target),
            app(TransformResolver::class),
            app(DateFormatDetector::class),
        );
    }

    /**
     * Keeps the rows for the rest of the session, bound to the user who
     * uploaded them. Also the way tests put a page at the review step.
     *
     * @param array<int, array<string, mixed>> $rows
     */
    public function setRows(array $rows): void
    {
        $this->sheetKey = sprintf('import-mapping:%s:%s', Authentication::user()->id->toString(), Str::uuid()->toString());
        $this->review = null;

        Cache::put($this->sheetKey, $rows, now()->addMinutes(Config::integer('import.mapping.sheet_ttl_minutes')));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function storedRows(): array
    {
        $rows = $this->sheetKey === null ? [] : Cache::get($this->sheetKey);

        /** @var array<int, array<string, mixed>> $rows */
        $rows = is_array($rows) ? $rows : [];

        return $rows;
    }

    /**
     * Resolves the selected register, rejecting anything not in the registry.
     */
    private function importTarget(): ImportTarget
    {
        $target = ImportTarget::tryFrom($this->target);

        if ($target === null || !$target->enabled()) {
            abort(403);
        }

        return $target;
    }

    private function failed(string $title, string $body): void
    {
        Notification::make()
            ->title($title)
            ->body($body)
            ->danger()
            ->send();
    }
}
