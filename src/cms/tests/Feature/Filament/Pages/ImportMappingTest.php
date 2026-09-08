<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Enums\Import\ImportTarget;
use App\Enums\Import\MappingConfidence;
use App\Facades\Authentication;
use App\Filament\Pages\ImportMapping;
use App\Import\ImportFailedException;
use App\Import\Mapping\DryRunner;
use App\Import\Mapping\EditableMapping;
use App\Import\Mapping\MappedRecordWriter;
use App\Import\Mapping\MappingAnalyser;
use App\Import\Mapping\MappingProfile;
use App\Import\Mapping\MappingProfileRepository;
use App\Import\Mapping\SheetReader;
use App\Import\ZipImporter;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\DataBreachRecord;
use App\Models\Processor;
use Filament\Notifications\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\RoleTestHelper;

/**
 * Drives the page directly: the wizard state lives on the component, so each
 * step can be exercised without going through file uploads.
 *
 * @param array<int, array<string, mixed>> $rows
 * @param array<string, array<string, string>> $mapping
 */
function pageAtReview(array $rows, array $mapping): ImportMapping
{
    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::DataBreachRecord->value;
    $page->headers = array_keys($rows[0]);
    $page->setRows($rows);
    $page->mapping = $mapping;
    $page->step = ImportMapping::STEP_REVIEW;

    return $page;
}

function breachRows(): array
{
    return [
        [
            'Naam' => 'Mail naar verkeerde ontvanger',
            'Type' => 'Definitief',
            'Datum melding' => '2026-03-04T00:00:00',
            'Gemeld AP' => 'ja',
            'Gemeld FG' => 'nee',
            'Gemeld betrokkene' => 'ja',
        ],
    ];
}

function breachMapping(): array
{
    // No 'transform': it follows from the target field, not from the mapping.
    return [
        'Naam' => ['target' => 'name'],
        'Type' => ['target' => 'type'],
        'Datum melding' => ['target' => 'reported_at'],
        'Gemeld AP' => ['target' => 'ap_reported'],
        'Gemeld FG' => ['target' => 'fg_reported'],
        'Gemeld betrokkene' => ['target' => 'reported_to_involved'],
    ];
}

it('loads the import mapping page', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(ImportMapping::getUrl(tenant: $organisation))
        ->assertSee(__('import_mapping.help'));
});

it('renders through livewire without hydration errors', function (): void {
    // The page is driven directly elsewhere in this file, which skips Livewire's
    // hydration; this exercises the real request path.
    $this->asFilamentUser()
        ->createLivewireTestable(ImportMapping::class)
        ->assertOk()
        ->assertSet('step', ImportMapping::STEP_UPLOAD);
});

it('proposes a mapping from an uploaded sheet', function (): void {
    $this->asFilamentUser();

    /** @var SheetReader $sheetReader */
    $sheetReader = $this->app->get(SheetReader::class);
    $sheet = $sheetReader->read('datalekken.csv', "Naam,Samenvatting incident\nEerste,Mail verkeerd\n");

    /** @var MappingAnalyser $analyser */
    $analyser = $this->app->get(MappingAnalyser::class);
    $profile = $analyser->analyse(ImportTarget::DataBreachRecord, $sheet->headers);

    $targets = [];
    foreach ($profile->fields as $field) {
        $targets[$field->source] = $field->target;
    }

    expect($targets)->toBe(['Naam' => 'name', 'Samenvatting incident' => 'summary']);
});

it('reports issues on dry run without importing anything', function (): void {
    $this->asFilamentUser();

    $rows = breachRows();
    $rows[0]['Gemeld AP'] = 'misschien';

    $page = pageAtReview($rows, breachMapping());
    $before = DataBreachRecord::query()->count();

    $page->dryRun($this->app->get(DryRunner::class));

    expect($page->result['issues'])->toHaveCount(1)
        ->and($page->result['fits'])->toBe(0)
        ->and(DataBreachRecord::query()->count())->toBe($before);
});

it('imports the rows that fit', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());
    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $record = DataBreachRecord::query()->where('name', 'Mail naar verkeerde ontvanger')->first();

    expect($page->result['imported'])->toBe(1)
        ->and($page->step)->toBe(ImportMapping::STEP_RESULT)
        ->and($record?->type)->toBe('Definitief')
        ->and($record?->ap_reported)->toBeTrue()
        ->and($record?->fg_reported)->toBeFalse();
});

it('leaves problem rows out of the import', function (): void {
    $this->asFilamentUser();

    $rows = breachRows();
    $rows[] = [...breachRows()[0], 'Naam' => null, 'Type' => 'Voorlopig'];

    $page = pageAtReview($rows, breachMapping());
    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    expect($page->result['imported'])->toBe(1);
});

it('saves the mapping for reuse when a name is given', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());
    $page->profileName = 'Zenya VIM-export';
    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    /** @var MappingProfileRepository $repository */
    $repository = $this->app->get(MappingProfileRepository::class);
    $saved = $repository->findByFingerprint(MappingProfile::fingerprint($page->headers), Authentication::organisation()->id);

    expect($saved?->name)->toBe('Zenya VIM-export');
});

it('does not save a profile when no name is given', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());
    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    /** @var MappingProfileRepository $repository */
    $repository = $this->app->get(MappingProfileRepository::class);

    expect($repository->findByFingerprint(MappingProfile::fingerprint($page->headers), Authentication::organisation()->id))->toBeNull();
});

it('offers an ignore option for every column', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());

    expect($page->review()->options()->flat())->toHaveKey('')
        ->and($page->review()->options()->flat()[''])->toBe(__('import_mapping.ignore'));
});

it('refuses a target that is not in the registry', function (): void {
    $this->asFilamentUser();

    // $target is a public Livewire property, so the browser controls it: a class
    // name coming from outside must never reach `new $class()`.
    $page = pageAtReview(breachRows(), breachMapping());
    $page->target = 'App\\Models\\User';

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );
})->throws(HttpException::class);

it('refuses an arbitrary class name as target', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());
    $page->target = 'Illuminate\\Support\\Facades\\Artisan';

    $page->review()->options()->flat();
})->throws(HttpException::class);

it('labels target fields in dutch, without the attribute name', function (): void {
    $this->asFilamentUser();

    $options = pageAtReview(breachRows(), breachMapping())->review()->options()->flat();

    expect($options['reported_at'])->toBe('Datum melding')
        ->and($options['summary'])->toBe('Samenvatting incident');
});

it('distinguishes fields that share the label "Namelijk"', function (): void {
    $this->asFilamentUser();

    $options = pageAtReview(breachRows(), breachMapping())->review()->options()->flat();

    expect($options['nature_of_incident_other'])->toBe('Aard van incident — Namelijk')
        ->and($options['personal_data_categories_other'])
        ->toBe('Categorieën van persoonsgegevens — Namelijk');
});

it('does not offer internally managed attributes', function (): void {
    $this->asFilamentUser();

    $options = pageAtReview(breachRows(), breachMapping())->review()->options()->flat();

    expect($options)->not->toHaveKey('organisation_id')
        ->and($options)->not->toHaveKey('entity_number_id');
});

it('groups target fields the way the register form groups them', function (): void {
    $this->asFilamentUser();

    $groups = pageAtReview(breachRows(), breachMapping())->review()->options()->grouped();

    expect($groups)->toHaveKey(__('data_breach_record.step_dates'))
        ->and($groups[__('data_breach_record.step_dates')])->toHaveKey('discovered_at');
});

it('shows distinct sample values per column', function (): void {
    $this->asFilamentUser();

    $rows = [...breachRows(), [...breachRows()[0], 'Naam' => 'Tweede melding']];
    $page = pageAtReview($rows, breachMapping());

    expect($page->review()->column('Naam')->samples())
        ->toBe(['Mail naar verkeerde ontvanger', 'Tweede melding']);
});

it('shows a fresh analysis in full, however confident', function (): void {
    $this->asFilamentUser();

    // Nothing is hidden when the mapping has not been reviewed before.
    $page = pageAtReview(breachRows(), breachMapping());
    $page->mapping['Naam']['confidence'] = MappingConfidence::Exact->value;

    expect($page->review()->settledHeaders())->toBeEmpty()
        ->and($page->review()->unsettledHeaders())->toContain('Naam');
});

it('collapses the columns that came from a recognised profile', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());
    $page->recognisedProfile = 'Zenya VIM-export';

    expect($page->review()->settledHeaders())->toContain('Naam')
        ->and($page->review()->unsettledHeaders())->not->toContain('Naam');
});

it('offers a date choice when a yes/no column feeds a date field', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(
        [['Melding AP' => 'ja'], ['Melding AP' => 'nee']],
        ['Melding AP' => ['target' => 'ap_reported_at']],
    );

    expect($page->review()->column('Melding AP')->needsTrueDate())->toBeTrue();
});

it('does not offer a date choice for a real date column', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(
        [['Datum melding' => '2026-06-02T00:00:00']],
        ['Datum melding' => ['target' => 'reported_at']],
    );

    expect($page->review()->column('Datum melding')->needsTrueDate())->toBeFalse();
});

it('imports a yes as the chosen date and a no as empty', function (): void {
    $this->asFilamentUser();

    $row = [
        'Type' => 'Definitief',
        'Gemeld AP' => 'ja',
        'Gemeld FG' => 'ja',
        'Gemeld betrokkene' => 'nee',
    ];

    $page = pageAtReview(
        [
            [...$row, 'Naam' => 'Wel gemeld', 'Melding AP' => 'ja'],
            [...$row, 'Naam' => 'Niet gemeld', 'Melding AP' => 'nee'],
        ],
        [
            'Naam' => ['target' => 'name'],
            'Type' => ['target' => 'type'],
            'Gemeld AP' => ['target' => 'ap_reported'],
            'Gemeld FG' => ['target' => 'fg_reported'],
            'Gemeld betrokkene' => ['target' => 'reported_to_involved'],
            'Melding AP' => [
                'target' => 'ap_reported_at',
                'true_date_mode' => 'fixed',
                'true_date' => '2026-06-02',
            ],
        ],
    );

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $reported = DataBreachRecord::query()->where('name', 'Wel gemeld')->first();
    $notReported = DataBreachRecord::query()->where('name', 'Niet gemeld')->first();

    expect($reported?->ap_reported_at?->format('Y-m-d'))->toBe('2026-06-02')
        ->and($notReported?->ap_reported_at)->toBeNull();
});

it('does not claim no choice was made when the analyser filled one in', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());
    $page->mapping['Naam']['confidence'] = MappingConfidence::Exact->value;

    expect($page->review()->column('Naam')->statusLabel())->toBe(__('import_mapping.status_suggested_strong'))
        ->and($page->review()->column('Naam')->needsAttention())->toBeFalse();
});

it('marks a weak suggestion as worth checking', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());
    $page->mapping['Naam']['confidence'] = MappingConfidence::Label->value;

    expect($page->review()->column('Naam')->statusLabel())->toBe(__('import_mapping.status_suggested_weak'))
        ->and($page->review()->column('Naam')->needsAttention())->toBeTrue();
});

it('reports an empty column as still open', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());
    $page->mapping['Naam']['target'] = '';

    expect($page->review()->column('Naam')->statusLabel())->toBe(__('import_mapping.status_open'))
        ->and($page->review()->column('Naam')->needsAttention())->toBeTrue();
});

it('links a column to shared entities, creating them once', function (): void {
    $this->asFilamentUser();

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Verwerkers'];
    $page->setRows([
        ['Naam' => 'Eerste verwerking', 'Verwerkers' => "Firma A\nFirma B"],
        ['Naam' => 'Tweede verwerking', 'Verwerkers' => 'Firma A'],
    ]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Verwerkers' => ['target' => 'processors'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $first = AvgResponsibleProcessingRecord::query()->where('name', 'Eerste verwerking')->first();
    $second = AvgResponsibleProcessingRecord::query()->where('name', 'Tweede verwerking')->first();

    expect($page->result['imported'])->toBe(2)
        ->and($first?->processors()->pluck('name')->sort()->values()->all())->toBe(['Firma A', 'Firma B'])
        ->and($second?->processors()->pluck('name')->all())->toBe(['Firma A'])
        // "Firma A" appears in both rows but must be a single record.
        ->and(Processor::query()->where('name', 'Firma A')->count())->toBe(1);
});

it('reports which entities the import created', function (): void {
    $this->asFilamentUser();

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Verwerkers'];
    $page->setRows([['Naam' => 'Een verwerking', 'Verwerkers' => 'Nieuwe Firma']]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Verwerkers' => ['target' => 'processors'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    expect($page->result['entities']['new'][Processor::class] ?? [])->toContain('Nieuwe Firma');
});

it('offers relations as mapping targets', function (): void {
    $this->asFilamentUser();

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;

    expect($page->review()->options()->flat())->toHaveKey('processors')
        ->and($page->review()->options()->flat())->toHaveKey('systems');
});

it('offers the processing registers a data breach can link to', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());

    expect($page->review()->options()->flat())->toHaveKey('avgResponsibleProcessingRecords')
        ->and($page->review()->options()->flat())->toHaveKey('wpgProcessingRecords');
});

it('does not create a processing record that does not exist', function (): void {
    $this->asFilamentUser();

    // A data breach naming an unknown processing record should flag it, not add
    // an empty register entry.
    $page = pageAtReview(
        [[...breachRows()[0], 'Verwerkingen' => 'Bestaat Niet']],
        [...breachMapping(), 'Verwerkingen' => ['target' => 'avgResponsibleProcessingRecords']],
    );

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    expect($page->result['entities']['unresolved'][AvgResponsibleProcessingRecord::class] ?? [])
        ->toContain('Bestaat Niet')
        ->and(AvgResponsibleProcessingRecord::query()->where('name', 'Bestaat Niet')->count())->toBe(0);
});

it('links to a processing record that does exist', function (): void {
    $this->asFilamentUser();

    AvgResponsibleProcessingRecord::factory()->create([
        'organisation_id' => Authentication::organisation()->id,
        'name' => 'Clientadministratie',
    ]);

    $page = pageAtReview(
        [[...breachRows()[0], 'Verwerkingen' => 'Clientadministratie']],
        [...breachMapping(), 'Verwerkingen' => ['target' => 'avgResponsibleProcessingRecords']],
    );

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $breach = DataBreachRecord::query()->where('name', 'Mail naar verkeerde ontvanger')->first();

    expect($breach?->avgResponsibleProcessingRecords()->pluck('name')->all())
        ->toContain('Clientadministratie');
});

it('analyses a single upload without a separate button', function (): void {
    $this->asFilamentUser();

    // A FileUpload without ->multiple() hands over one TemporaryUploadedFile
    // rather than an array; the page has to cope with both shapes.
    Storage::fake('local');
    $file = UploadedFile::fake()->createWithContent(
        'zenya.csv',
        "Onderwerp,Soort melding\nMail verkeerd,Definitief\n",
    );

    $this->createLivewireTestable(ImportMapping::class)
        ->set('target', ImportTarget::DataBreachRecord->value)
        ->set('files', $file)
        ->assertSet('step', ImportMapping::STEP_REVIEW)
        ->assertSet('headers', ['Onderwerp', 'Soort melding']);
});

it('renders after a restart', function (): void {
    $this->asFilamentUser();

    // Livewire rehydrates without calling mount(), so reading an uninitialised
    // typed property here used to throw.
    $this->createLivewireTestable(ImportMapping::class)
        ->call('restart')
        ->assertOk()
        ->assertSet('step', ImportMapping::STEP_UPLOAD);
});

it('maps several source columns onto the same relation', function (): void {
    $this->asFilamentUser();

    // Sources often spread one relation over several columns, e.g. a column per
    // supplier rather than one cell holding all of them.
    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Verwerker 1', 'Verwerker 2'];
    $page->setRows([['Naam' => 'Een verwerking', 'Verwerker 1' => 'Firma A', 'Verwerker 2' => 'Firma B']]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Verwerker 1' => ['target' => 'processors'],
        'Verwerker 2' => ['target' => 'processors'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $record = AvgResponsibleProcessingRecord::query()->where('name', 'Een verwerking')->first();

    expect($record?->processors()->pluck('name')->sort()->values()->all())
        ->toBe(['Firma A', 'Firma B']);
});

it('builds one related record from several columns', function (): void {
    $this->asFilamentUser();

    // "Verwerker naam" and "Verwerker e-mail" describe the same supplier, so
    // they have to end up on one record rather than two.
    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Verwerker naam', 'Verwerker e-mail'];
    $page->setRows([
        [
            'Naam' => 'Een verwerking',
            'Verwerker naam' => 'Firma A',
            'Verwerker e-mail' => 'contact@firma-a.nl',
        ]]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Verwerker naam' => ['target' => 'processors'],
        'Verwerker e-mail' => ['target' => 'processors::email'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $processor = Processor::query()->where('name', 'Firma A')->first();

    expect($processor?->email)->toBe('contact@firma-a.nl')
        ->and(Processor::query()->where('name', 'Firma A')->count())->toBe(1);
});

it('keeps columns aligned when a cell holds several entities', function (): void {
    $this->asFilamentUser();

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Verwerkers', 'E-mailadressen'];
    $page->setRows([
        [
            'Naam' => 'Een verwerking',
            'Verwerkers' => "Firma A\nFirma B",
            'E-mailadressen' => "a@example.org\nb@example.org",
        ]]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Verwerkers' => ['target' => 'processors'],
        'E-mailadressen' => ['target' => 'processors::email'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    expect(Processor::query()->where('name', 'Firma A')->first()?->email)->toBe('a@example.org')
        ->and(Processor::query()->where('name', 'Firma B')->first()?->email)->toBe('b@example.org');
});

it('offers relation attributes as their own targets', function (): void {
    $this->asFilamentUser();

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;

    expect($page->review()->options()->flat())->toHaveKey('processors::email')
        ->and($page->review()->options()->flat()['processors::email'])->toContain('E-mail');
});

it('fills a lookup list, adding an unknown value to it', function (): void {
    $this->asFilamentUser();

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Dienst'];
    $page->setRows([['Naam' => 'Een verwerking', 'Dienst' => 'Wijkverpleging']]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Dienst' => ['target' => 'lookup:service'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $record = AvgResponsibleProcessingRecord::query()->where('name', 'Een verwerking')->first();

    expect(AvgResponsibleProcessingRecordService::query()->where('name', 'Wijkverpleging')->count())->toBe(1)
        ->and($record?->avg_responsible_processing_record_service_id)->not->toBeNull();
});

it('reuses an existing lookup value', function (): void {
    $this->asFilamentUser();

    AvgResponsibleProcessingRecordService::factory()->create([
        'organisation_id' => Authentication::organisation()->id,
        'name' => 'Wijkverpleging',
    ]);

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Dienst'];
    $page->setRows([['Naam' => 'Een verwerking', 'Dienst' => 'Wijkverpleging']]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Dienst' => ['target' => 'lookup:service'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    expect(AvgResponsibleProcessingRecordService::query()->where('name', 'Wijkverpleging')->count())->toBe(1);
});

it('builds the address of a related record from separate columns', function (): void {
    $this->asFilamentUser();

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Verwerker', 'Straat', 'Postcode', 'Plaats'];
    $page->setRows([
        [
            'Naam' => 'Een verwerking',
            'Verwerker' => 'Firma A',
            'Straat' => 'Dorpsstraat 1',
            'Postcode' => '1234 AB',
            'Plaats' => 'Haarlem',
        ]]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Verwerker' => ['target' => 'processors'],
        'Straat' => ['target' => 'processors::address.address'],
        'Postcode' => ['target' => 'processors::address.postal_code'],
        'Plaats' => ['target' => 'processors::address.city'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $address = Processor::query()->where('name', 'Firma A')->first()?->address;

    expect($address?->address)->toBe('Dorpsstraat 1')
        ->and($address?->postal_code)->toBe('1234 AB')
        ->and($address?->city)->toBe('Haarlem');
});

it('offers lookup lists and sub-record fields as targets', function (): void {
    $this->asFilamentUser();

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;

    expect($page->review()->options()->flat())->toHaveKey('lookup:service')
        ->and($page->review()->options()->flat())->toHaveKey('processors::address.postal_code');
});

it('recognises a native archive and shows what it holds', function (): void {
    $this->asFilamentUser();

    // The file decides the route: a zip goes to the confirmation step rather
    // than the column mapping, because its layout is already known.
    Storage::fake('local');
    $path = sprintf('%s/%s.zip', sys_get_temp_dir(), Str::uuid()->toString());

    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE);
    $zip->addFromString(
        sprintf('Datalekken%sdatalekken.json', DIRECTORY_SEPARATOR),
        json_encode([['Naam' => 'Een datalek'], ['Naam' => 'Nog een datalek']]),
    );
    $zip->close();

    $file = UploadedFile::fake()->createWithContent('export.zip', (string) file_get_contents($path));
    unlink($path);

    $this->createLivewireTestable(ImportMapping::class)
        ->set('files', $file)
        ->assertSet('step', ImportMapping::STEP_ARCHIVE)
        ->assertSet('archiveContents', ['Datalekken' => 2]);
});

it('sends a spreadsheet to the mapping step instead', function (): void {
    $this->asFilamentUser();

    Storage::fake('local');
    $file = UploadedFile::fake()->createWithContent(
        'zenya.csv',
        "Onderwerp,Soort melding\nMail verkeerd,Definitief\n",
    );

    $this->createLivewireTestable(ImportMapping::class)
        ->set('target', ImportTarget::DataBreachRecord->value)
        ->set('files', $file)
        ->assertSet('step', ImportMapping::STEP_REVIEW)
        ->assertSet('archiveContents', []);
});

it('starts over on restart', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());
    $page->restart();

    expect($page->step)->toBe(ImportMapping::STEP_UPLOAD)
        ->and($page->headers)->toBeEmpty()
        ->and($page->sheetKey)->toBeNull();
});

it('skips rows whose source reference was imported before', function (): void {
    $this->asFilamentUser();

    // The designed workflow is apply, fix the problem rows, apply again; the
    // rows that went in the first time must not go in twice.
    $rows = [[...breachRows()[0], 'Meldnummer' => 'VIM-2026-0413']];
    $mapping = [...breachMapping(), 'Meldnummer' => ['target' => 'import_id']];

    $page = pageAtReview($rows, $mapping);
    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $again = pageAtReview($rows, $mapping);
    $again->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    expect($page->result['imported'])->toBe(1)
        ->and($again->result['imported'])->toBe(0)
        ->and($again->result['skipped'])->toBe(1)
        ->and(DataBreachRecord::query()->where('import_id', 'VIM-2026-0413')->count())->toBe(1);
});

it('reports a row that could not be written instead of hiding it', function (): void {
    $this->asFilamentUser();

    // A count beyond what the integer column holds passes the dry-run (it is
    // a whole number) and is refused by the database.
    $rows = [
        [...breachRows()[0], 'Aantal' => '12'],
        [...breachRows()[0], 'Aantal' => '99999999999'],
    ];
    $mapping = [...breachMapping(), 'Aantal' => ['target' => 'affected_count']];

    $page = pageAtReview($rows, $mapping);
    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    expect($page->result['imported'])->toBe(1)
        ->and($page->result['failures'])->toHaveCount(1)
        ->and($page->result['failures'][0]['row'])->toBe(2)
        ->and($page->result['failures'][0]['reason'])->toBe(__('import_mapping.issue.write_failed_out_of_range'))
        ->and(DataBreachRecord::query()->where('name', 'Mail naar verkeerde ontvanger')->count())->toBe(1);
});

it('catches a value too long for its column before anything is written', function (): void {
    $this->asFilamentUser();

    $rows = [
        breachRows()[0],
        [...breachRows()[0], 'Naam' => str_repeat('x', 300)],
    ];

    $page = pageAtReview($rows, breachMapping());
    $page->dryRun($this->app->get(DryRunner::class));

    expect($page->result['fits'])->toBe(1)
        ->and($page->result['issues'])->toHaveCount(1)
        ->and($page->result['issues'][0]['reason'])->toBe(__('import_mapping.issue.too_long', ['field' => 'Naam', 'max' => 255]))
        ->and(DataBreachRecord::query()->count())->toBe(0);
});

it('refuses a mapping target the screen never offered', function (): void {
    $this->asFilamentUser();

    // $mapping is editable from the browser; a target outside the offered list
    // is tampering, not a choice.
    $page = pageAtReview(breachRows(), [...breachMapping(), 'Type' => ['target' => 'organisation_id']]);

    expect(fn () => $page->dryRun($this->app->get(DryRunner::class)))
        ->toThrow(HttpException::class);
});

it('does not offer the workflow state or foreign keys as targets', function (): void {
    $this->asFilamentUser();

    $options = pageAtReview(breachRows(), breachMapping())->review()->options()->flat();

    expect($options)->not->toHaveKey('state')
        ->and($options)->not->toHaveKey('organisation_id')
        ->and($options)->not->toHaveKey('entity_number_id')
        ->and($options)->toHaveKey('import_id');
});

it('refuses to import for a user without the import permission', function (): void {
    $this->asFilamentUser();
    $page = pageAtReview(breachRows(), breachMapping());

    RoleTestHelper::actAs([Role::INPUT_PROCESSOR]);

    expect(
        fn () => $page->apply(
            $this->app->get(DryRunner::class),
            $this->app->get(MappedRecordWriter::class),
            $this->app->get(MappingProfileRepository::class),
        ),
    )
        ->toThrow(HttpException::class);
});

it('sends the user back to the start when the uploaded rows have expired', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());
    Cache::forget((string) $page->sheetKey);

    $page->dryRun($this->app->get(DryRunner::class));

    expect($page->step)->toBe(ImportMapping::STEP_UPLOAD)
        ->and($page->sheetKey)->toBeNull();
    Notification::assertNotified(__('import_mapping.read_failed'));
});

it('keeps the rows out of the livewire payload', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());

    expect($page->sheetKey)->toStartWith('import-mapping:')
        ->and(Cache::get((string) $page->sheetKey))->toBe(breachRows())
        ->and($page->review()->rowCount())->toBe(1);
});

it('tells the user when the sheet holds only a header row', function (): void {
    $this->asFilamentUser();

    Storage::fake('local');
    $file = UploadedFile::fake()->createWithContent('leeg.csv', "Onderwerp,Soort melding\n");

    $this->createLivewireTestable(ImportMapping::class)
        ->set('target', ImportTarget::DataBreachRecord->value)
        ->set('files', $file)
        ->assertSet('step', ImportMapping::STEP_UPLOAD)
        ->assertNotified(__('import_mapping.read_failed'));
});

/**
 * @param array<string, string> $entries
 */
function nativeArchive(array $entries): UploadedFile
{
    $path = sprintf('%s/%s.zip', sys_get_temp_dir(), Str::uuid()->toString());
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE);
    foreach ($entries as $name => $contents) {
        $zip->addFromString($name, $contents);
    }
    $zip->close();

    $file = UploadedFile::fake()->createWithContent('export.zip', (string) file_get_contents($path));
    unlink($path);

    return $file;
}

it('hands a confirmed archive to the existing importer', function (): void {
    $this->asFilamentUser();
    Storage::fake('local');

    $this->mock(ZipImporter::class)
        ->shouldReceive('importFiles')
        ->once();

    $this->createLivewireTestable(ImportMapping::class)
        ->set('files', nativeArchive(['Datalekken/datalekken.json' => json_encode([['Naam' => 'Een']])]))
        ->assertSet('step', ImportMapping::STEP_ARCHIVE)
        ->call('applyArchive')
        ->assertSet('step', ImportMapping::STEP_RESULT)
        ->assertSet('result.imported', 1)
        ->assertNotified(__('import.upload_success'));
});

it('reports an archive the importer rejects without leaking its message', function (): void {
    $this->asFilamentUser();
    Storage::fake('local');

    $this->mock(ZipImporter::class)
        ->shouldReceive('importFiles')
        ->once()
        ->andThrow(new ImportFailedException('/tmp/secret-path.zip is broken'));

    $this->createLivewireTestable(ImportMapping::class)
        ->set('files', nativeArchive(['Datalekken/datalekken.json' => json_encode([['Naam' => 'Een']])]))
        ->call('applyArchive')
        ->assertSet('step', ImportMapping::STEP_ARCHIVE)
        ->assertNotified(__('import.failed'));
});

it('tells the user when an archive holds no register it knows', function (): void {
    $this->asFilamentUser();
    Storage::fake('local');

    $this->createLivewireTestable(ImportMapping::class)
        ->set('files', nativeArchive(['random/readme.txt' => 'hello']))
        ->assertSet('step', ImportMapping::STEP_UPLOAD)
        ->assertNotified(__('import_mapping.archive_empty'));
});

it('sends the user back to the start when applying after the rows expired', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), breachMapping());
    Cache::forget((string) $page->sheetKey);

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    expect($page->step)->toBe(ImportMapping::STEP_UPLOAD);
});

it('describes a column the way the review screen shows it', function (): void {
    $this->asFilamentUser();

    $mapping = breachMapping();
    $mapping['Verwerkingen'] = ['target' => 'avgResponsibleProcessingRecords'];
    $mapping['Los'] = ['target' => ''];
    $mapping['Gemeld AP'] = ['target' => 'ap_reported_at', 'true_date_mode' => 'today'];

    $page = pageAtReview([[...breachRows()[0], 'Verwerkingen' => 'Een verwerking', 'Los' => 'x']], $mapping);
    $review = $page->review();

    expect($review->column('Naam')->targetLabel())->toBe(__('data_breach_record.name'))
        ->and($review->column('Los')->targetLabel())->toBe(__('import_mapping.ignore'))
        ->and($review->column('Los')->transformLabel())->toBe('')
        ->and($review->column('Verwerkingen')->transformLabel())->toBe(__('import_mapping.transform.relation'))
        ->and($review->column('Gemeld AP')->needsTrueDate())->toBeTrue()
        ->and($review->column('Gemeld AP')->trueDate())->toBeNull()
        ->and($review->toProfile()->unmapped)->toBe(['Los']);
});

it('shows an unknown target by its raw name rather than crashing the screen', function (): void {
    $this->asFilamentUser();

    $page = pageAtReview(breachRows(), [...breachMapping(), 'Naam' => ['target' => 'bogus']]);

    expect($page->review()->column('Naam')->targetLabel())->toBe('bogus');
});

it('samples at most a handful of distinct values, flattening nested ones', function (): void {
    $this->asFilamentUser();

    $rows = [];
    for ($i = 0; $i < 8; $i++) {
        $rows[] = ['Naam' => sprintf('Naam %d', $i), 'Adres' => ['straat' => sprintf('Straat %d', $i)]];
    }

    $page = pageAtReview($rows, ['Naam' => ['target' => 'name'], 'Adres' => ['target' => '']]);

    expect($page->review()->column('Naam')->samples(10))->toHaveCount(5)
        ->and($page->review()->column('Adres')->samples())->toBe(['Straat 0', 'Straat 1', 'Straat 2']);
});

it('leaves lookups and relations alone when the cell is empty', function (): void {
    $this->asFilamentUser();

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Dienst', 'Verwerkers'];
    $page->setRows([['Naam' => 'Een verwerking', 'Dienst' => null, 'Verwerkers' => null]]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Dienst' => ['target' => 'lookup:service'],
        'Verwerkers' => ['target' => 'processors'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $record = AvgResponsibleProcessingRecord::query()->where('name', 'Een verwerking')->first();

    expect($page->result['imported'])->toBe(1)
        ->and($record?->avg_responsible_processing_record_service_id)->toBeNull()
        ->and($record?->processors()->count())->toBe(0);
});

it('updates the address of a processor that already has one', function (): void {
    $this->asFilamentUser();

    $processor = Processor::factory()->create([
        'organisation_id' => Authentication::organisation()->id,
        'name' => 'Firma A',
    ]);
    $processor->address()->create(['address' => 'Oude straat 1', 'postal_code' => '1111 AA', 'city' => 'Oud', 'country' => 'NL']);

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Verwerker', 'Plaats'];
    $page->setRows([['Naam' => 'Een verwerking', 'Verwerker' => 'Firma A', 'Plaats' => 'Nieuw']]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Verwerker' => ['target' => 'processors'],
        'Plaats' => ['target' => 'processors::address.city'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    expect($processor->fresh()?->address?->city)->toBe('Nieuw')
        ->and($processor->fresh()?->address?->address)->toBe('Oude straat 1');
});

it('falls back to a readable attribute name when no label exists', function (): void {
    $this->asFilamentUser();

    // A translation that still looks like a key counts as missing.
    Lang::addLines(['data_breach_record.involved_people' => 'data_breach_record.involved_people'], 'nl');

    expect(pageAtReview(breachRows(), breachMapping())->review()->options()->flat()['involved_people'])
        ->toBe('Involved people');
});

it('asks which way round an ambiguous date is, and refuses to guess', function (): void {
    $this->asFilamentUser();

    $rows = [[...breachRows()[0], 'Datum melding' => '04-03-2026']];
    $page = pageAtReview($rows, breachMapping());
    $column = $page->review()->column('Datum melding');

    expect($column->needsDateFormat())->toBeTrue()
        ->and($column->dateFormatCandidates())->toBe(['d-m-Y', 'm-d-Y'])
        ->and($column->dateFormatExamples()['d-m-Y'])->toStartWith('4 maart 2026')
        ->and($column->dateFormatExamples()['m-d-Y'])->toStartWith('3 april 2026');

    $page->dryRun($this->app->get(DryRunner::class));

    expect($page->result['fits'])->toBe(0);
    Notification::assertNotified(__('import_mapping.review_heading'));
});

it('reads the whole column in the format the user chose', function (): void {
    $this->asFilamentUser();

    $rows = [[...breachRows()[0], 'Datum melding' => '04-03-2026']];
    $mapping = breachMapping();
    $mapping['Datum melding'] = ['target' => 'reported_at', 'date_format' => 'm-d-Y'];

    $page = pageAtReview($rows, $mapping);
    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $record = DataBreachRecord::query()->where('name', 'Mail naar verkeerde ontvanger')->first();

    expect($record?->reported_at?->format('Y-m-d'))->toBe('2026-04-03');
});

it('decides the date format itself when the values allow only one', function (): void {
    $this->asFilamentUser();

    $rows = [[...breachRows()[0], 'Datum melding' => '13-03-2026']];
    $page = pageAtReview($rows, breachMapping());
    $column = $page->review()->column('Datum melding');

    expect($column->needsDateFormat())->toBeFalse()
        ->and($column->dateFormat())->toBe('d-m-Y')
        ->and($column->dateFormatExamples())->toHaveKey('d-m-Y')
        ->and($page->review()->toProfile()->fields[2]->dateFormat)->toBe('d-m-Y');
});

it('carries the chosen date format into a saved profile and back', function (): void {
    $this->asFilamentUser();

    $rows = [[...breachRows()[0], 'Datum melding' => '04-03-2026']];
    $mapping = breachMapping();
    $mapping['Datum melding'] = ['target' => 'reported_at', 'date_format' => 'm-d-Y'];

    $page = pageAtReview($rows, $mapping);
    $page->profileName = 'Amerikaanse export';
    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    /** @var MappingProfileRepository $repository */
    $repository = $this->app->get(MappingProfileRepository::class);
    $saved = $repository->findByFingerprint(MappingProfile::fingerprint($page->headers), Authentication::organisation()->id);
    $editable = EditableMapping::fromProfile($page->headers, $saved->toMappingProfile());

    expect($editable['Datum melding']['date_format'])->toBe('m-d-Y');
});

it('refuses a register that is behind a feature flag that is off', function (): void {
    $this->asFilamentUser();
    Config::set('features.wpg', false);

    $page = pageAtReview(breachRows(), breachMapping());
    $page->target = ImportTarget::WpgProcessingRecord->value;

    expect(fn () => $page->review())->toThrow(HttpException::class);
});

it('starts fields the source lacks out as the form does instead of refusing the row', function (): void {
    $this->asFilamentUser();

    // This source has no "gemeld aan FG" and no "type" column; the form would
    // start them out as no and as the first option.
    $page = pageAtReview(
        [['Naam' => 'Zonder FG-kolom']],
        ['Naam' => ['target' => 'name']],
    );
    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $record = DataBreachRecord::query()->where('name', 'Zonder FG-kolom')->first();

    expect($page->result['imported'])->toBe(1)
        ->and($record?->fg_reported)->toBeFalse()
        ->and($record?->ap_reported)->toBeFalse()
        ->and($record?->type)->toBe(__('data_breach_record.type_options')[0])
        ->and($record?->nature_of_incident)->toBeNull();
});

it('refuses to run when two columns feed the same plain field', function (): void {
    $this->asFilamentUser();

    // The second column would silently replace the first; which one wins is
    // not something the user should have to guess.
    $page = pageAtReview(breachRows(), [...breachMapping(), 'Type' => ['target' => 'name']]);
    $page->dryRun($this->app->get(DryRunner::class));

    expect($page->result['fits'])->toBe(0)
        ->and($page->review()->duplicateTargets())->toBe(['name' => ['Naam', 'Type']]);
    Notification::assertNotified(__('import_mapping.review_heading'));
});

it('takes any number of columns for a link, but one for an attribute of it', function (): void {
    $this->asFilamentUser();

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Verwerker 1', 'Verwerker 2', 'E-mail 1', 'E-mail 2'];
    $page->setRows([['Naam' => 'Een', 'Verwerker 1' => 'A', 'Verwerker 2' => 'B', 'E-mail 1' => 'a@x', 'E-mail 2' => 'b@x']]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Verwerker 1' => ['target' => 'processors'],
        'Verwerker 2' => ['target' => 'processors'],
        'E-mail 1' => ['target' => 'processors::email'],
        'E-mail 2' => ['target' => 'processors::email'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    expect($page->review()->duplicateTargets())->toBe(['processors::email' => ['E-mail 1', 'E-mail 2']]);
});

it('keeps columns without a field of their own as notes on the record', function (): void {
    $this->asFilamentUser();

    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $page->headers = ['Naam', 'Tekst', 'Afdeling'];
    $page->setRows([
        ['Naam' => 'Salarisadministratie', 'Tekst' => 'Overgenomen uit het oude register.', 'Afdeling' => 'HR'],
        ['Naam' => 'Toegangsbeheer', 'Tekst' => null, 'Afdeling' => 'ICT'],
    ]);
    $page->mapping = [
        'Naam' => ['target' => 'name'],
        'Tekst' => ['target' => 'remarks'],
        'Afdeling' => ['target' => 'remarks'],
    ];
    $page->step = ImportMapping::STEP_REVIEW;

    $page->dryRun($this->app->get(DryRunner::class));
    expect($page->result['fits'])->toBe(2);

    $page->apply(
        $this->app->get(DryRunner::class),
        $this->app->get(MappedRecordWriter::class),
        $this->app->get(MappingProfileRepository::class),
    );

    $first = AvgResponsibleProcessingRecord::query()->where('name', 'Salarisadministratie')->first();
    $second = AvgResponsibleProcessingRecord::query()->where('name', 'Toegangsbeheer')->first();

    expect($page->result['imported'])->toBe(2)
        ->and($first?->remarks()->orderBy('body')->pluck('body')->all())->toBe([
            'Afdeling: HR',
            'Tekst: Overgenomen uit het oude register.',
        ])
        // An empty cell is not a note.
        ->and($second?->remarks()->pluck('body')->all())->toBe(['Afdeling: ICT']);
});

it('offers notes only to registers that keep them, and shows the target as such', function (): void {
    $this->asFilamentUser();

    $breach = new ImportMapping();
    $breach->mount();
    $breach->target = ImportTarget::DataBreachRecord->value;

    $processing = new ImportMapping();
    $processing->mount();
    $processing->target = ImportTarget::AvgResponsibleProcessingRecord->value;
    $processing->headers = ['Tekst'];
    $processing->setRows([['Tekst' => 'iets']]);
    $processing->mapping = ['Tekst' => ['target' => 'remarks']];

    expect($breach->review()->options()->flat())->not->toHaveKey('remarks')
        ->and($processing->review()->options()->flat())->toHaveKey('remarks')
        ->and($processing->review()->column('Tekst')->transformLabel())->toBe(__('import_mapping.transform.remark'));
});

it('leaves a field that takes a code rather than a label out of the targets', function (): void {
    $this->asFilamentUser();

    // data_collection_source is an enum; the export writes its label, which
    // the cast would refuse. The field keeps its default instead.
    $page = new ImportMapping();
    $page->mount();
    $page->target = ImportTarget::AvgResponsibleProcessingRecord->value;

    expect($page->review()->options()->flat())->not->toHaveKey('data_collection_source');
});
