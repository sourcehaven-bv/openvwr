<?php

declare(strict_types=1);

use App\Enums\CoreEntityDataCollectionSource;
use App\Enums\Import\ImportTarget;
use App\Facades\Authentication;
use App\Filament\Exports\AlgorithmRecordExporter;
use App\Filament\Exports\AvgProcessorProcessingRecordExporter;
use App\Filament\Exports\AvgResponsibleProcessingRecordExporter;
use App\Filament\Exports\DataBreachRecordExporter;
use App\Filament\Exports\Exporter;
use App\Filament\Exports\WpgProcessingRecordExporter;
use App\Filament\Pages\ImportMapping;
use App\Import\Mapping\DryRunner;
use App\Import\Mapping\EditableMapping;
use App\Import\Mapping\MappedRecordWriter;
use App\Import\Mapping\MappingAnalyser;
use App\Import\Mapping\MappingProfileRepository;
use App\Import\Mapping\SheetReader;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Algorithm\AlgorithmTheme;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\ContactPerson;
use App\Models\DataBreachRecord;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\Stakeholder;
use App\Models\Tag;
use App\Models\Wpg\WpgGoal;
use App\Models\Wpg\WpgProcessingRecord;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

/**
 * The register's own Excel export, built the way the download builds it: the
 * exporter's columns as header, one formatted row per record, written to a
 * workbook.
 *
 * @param class-string<Exporter> $exporterClass
 * @param array<int, Model> $records
 *
 * @return string the workbook contents
 */
function exportWorkbook(string $exporterClass, array $records): string
{
    $columnMap = [];
    foreach ($exporterClass::getColumns() as $column) {
        $columnMap[$column->getName()] = $column->getLabel();
    }

    $exporter = (new Export(['exporter' => $exporterClass]))->getExporter($columnMap, []);

    $path = tempnam(sys_get_temp_dir(), 'export') . '.xlsx';
    $writer = new Writer();
    $writer->openToFile($path);
    $writer->addRow(Row::fromValues(array_values($columnMap)));

    foreach ($records as $record) {
        $writer->addRow(Row::fromValues($exporter($record)));
    }

    $writer->close();

    $contents = (string) file_get_contents($path);
    unlink($path);

    return $contents;
}

/**
 * Uploads the workbook, takes the mapping the analyser proposes as it is,
 * and imports.
 */
function importWorkbook(ImportTarget $target, string $contents): ImportMapping
{
    $sheet = (new SheetReader())->read('export.xlsx', $contents);
    $profile = app(MappingAnalyser::class)->analyse($target, $sheet->headers, $sheet->rows);

    $page = new ImportMapping();
    $page->mount();
    $page->target = $target->value;
    $page->headers = $sheet->headers;
    $page->setRows($sheet->rows);
    $page->mapping = EditableMapping::fromProfile($sheet->headers, $profile);
    $page->step = ImportMapping::STEP_REVIEW;

    $page->apply(
        app(DryRunner::class),
        app(MappedRecordWriter::class),
        app(MappingProfileRepository::class),
    );

    return $page;
}

it('imports its own data breach export back, field for field, without a hand-made mapping', function (): void {
    $this->asFilamentUser();
    $organisationId = Authentication::organisation()->id;

    $responsible = Responsible::factory()->create(['organisation_id' => $organisationId, 'name' => 'Raad van Bestuur']);
    $original = DataBreachRecord::factory()->create([
        'organisation_id' => $organisationId,
        'name' => 'Mail met bijlage naar verkeerde ontvanger',
        'type' => 'Definitief',
        'reported_at' => '2026-03-04',
        'discovered_at' => '2026-03-03',
        'completed_at' => null,
        'ap_reported' => true,
        'fg_reported' => false,
        'reported_to_involved' => true,
        'summary' => 'Bijlage met cliëntgegevens naar een extern adres gestuurd.',
        'personal_data_categories' => ['Naam', 'E-mailadres', 'Adres en woonplaats'],
        'affected_count' => 12,
    ]);
    $original->responsibles()->attach($responsible);

    $page = importWorkbook(ImportTarget::DataBreachRecord, exportWorkbook(DataBreachRecordExporter::class, [$original]));

    $copy = DataBreachRecord::query()->whereKeyNot($original->id)->where('name', $original->name)->first();

    expect($page->result['imported'])->toBe(1)
        ->and($page->result['issues'])->toBe([])
        ->and($page->result['failures'])->toBe([])
        ->and($copy)->toBeInstanceOf(DataBreachRecord::class)
        ->and($copy?->type)->toBe('Definitief')
        ->and($copy?->reported_at?->toDateString())->toBe('2026-03-04')
        ->and($copy?->discovered_at?->toDateString())->toBe('2026-03-03')
        ->and($copy?->completed_at)->toBeNull()
        ->and($copy?->ap_reported)->toBeTrue()
        ->and($copy?->fg_reported)->toBeFalse()
        ->and($copy?->reported_to_involved)->toBeTrue()
        ->and($copy?->summary)->toBe($original->summary)
        ->and($copy?->personal_data_categories)->toBe(['Naam', 'E-mailadres', 'Adres en woonplaats'])
        ->and($copy?->affected_count)->toBe(12)
        ->and($copy?->responsibles()->pluck('name')->all())->toBe(['Raad van Bestuur'])
        // The export names the existing record; the import must find it, not add one.
        ->and(Responsible::query()->where('name', 'Raad van Bestuur')->count())->toBe(1);
});

it('imports its own processing register export back, links and lookups included', function (): void {
    $this->asFilamentUser();
    $organisationId = Authentication::organisation()->id;

    $original = AvgResponsibleProcessingRecord::factory()->create([
        'organisation_id' => $organisationId,
        'name' => 'Salarisadministratie',
        'data_collection_source' => CoreEntityDataCollectionSource::SECONDARY,
        'has_processors' => true,
        // The observer clears the security fields when has_security is off.
        'has_security' => true,
        'outside_eu' => false,
        'decision_making' => false,
        'logic' => null,
        'importance_consequences' => null,
        'measures_description' => 'Toegang op basis van rol; logging van inzage.',
        'avg_responsible_processing_record_service_id' => AvgResponsibleProcessingRecordService::factory()->create([
            'organisation_id' => $organisationId,
            'name' => 'HR',
        ])->id,
    ]);

    foreach (['Firma A', 'Firma B'] as $name) {
        $processor = Processor::factory()->create(['organisation_id' => $organisationId, 'name' => $name, 'email' => '']);
        $original->processors()->attach($processor);
    }

    // Only the second verwerker has details; the export must keep the blank
    // so the details are not read onto the first.
    $firmaB = Processor::query()->where('name', 'Firma B')->firstOrFail();
    $firmaB->update(['email' => 'info@firma-b.example']);
    $firmaB->address()->create(['address' => 'Stationsplein 1', 'postal_code' => '3511 ED', 'city' => 'Utrecht', 'country' => 'Nederland']);
    $breach = DataBreachRecord::factory()->create(['organisation_id' => $organisationId, 'name' => 'Mail naar verkeerde ontvanger']);
    $original->dataBreachRecords()->attach($breach);

    $original->receivers()->attach(Receiver::factory()->create(['organisation_id' => $organisationId, 'description' => 'Belastingdienst']));
    $original->stakeholders()->attach(
        Stakeholder::factory()->create(['organisation_id' => $organisationId, 'description' => 'Medewerkers']),
    );
    $original->avgGoals()->attach(AvgGoal::factory()->create([
        'organisation_id' => $organisationId,
        'goal' => 'Uitbetalen van salaris',
        'avg_goal_legal_base' => 'Wettelijke verplichting',
    ]));
    $original->contactPersons()->attach(ContactPerson::factory()->create([
        'organisation_id' => $organisationId,
        'name' => 'P. de Vries',
        'email' => 'p.devries@example.org',
    ]));
    $original->tags()->attach(Tag::factory()->create(['organisation_id' => $organisationId, 'name' => 'Kernproces']));

    $page = importWorkbook(
        ImportTarget::AvgResponsibleProcessingRecord,
        exportWorkbook(AvgResponsibleProcessingRecordExporter::class, [$original]),
    );

    $copy = AvgResponsibleProcessingRecord::query()->whereKeyNot($original->id)->where('name', 'Salarisadministratie')->first();

    expect($page->result['imported'])->toBe(1)
        ->and($page->result['issues'])->toBe([])
        ->and($page->result['failures'])->toBe([])
        ->and($copy)->toBeInstanceOf(AvgResponsibleProcessingRecord::class)
        ->and($copy?->has_processors)->toBeTrue()
        ->and($copy?->data_collection_source)->toBe(CoreEntityDataCollectionSource::SECONDARY)
        ->and($copy?->tags()->pluck('name')->all())->toBe(['Kernproces'])
        ->and($copy?->outside_eu)->toBeFalse()
        ->and($copy?->measures_description)->toBe('Toegang op basis van rol; logging van inzage.')
        ->and($copy?->avgResponsibleProcessingRecordService?->name)->toBe('HR')
        ->and($copy?->processors()->pluck('name')->sort()->values()->all())->toBe(['Firma A', 'Firma B'])
        ->and($firmaB->refresh()->email)->toBe('info@firma-b.example')
        ->and(Processor::query()->where('name', 'Firma A')->firstOrFail()->email)->toBe('')
        ->and($firmaB->address?->city)->toBe('Utrecht')
        ->and($copy?->receivers()->pluck('description')->all())->toBe(['Belastingdienst'])
        ->and($copy?->stakeholders()->pluck('description')->all())->toBe(['Medewerkers'])
        ->and($copy?->avgGoals()->pluck('goal')->all())->toBe(['Uitbetalen van salaris'])
        ->and(AvgGoal::query()->where('goal', 'Uitbetalen van salaris')->firstOrFail()->avg_goal_legal_base)->toBe(
            'Wettelijke verplichting',
        )
        ->and($copy?->contactPersons()->pluck('name')->all())->toBe(['P. de Vries'])
        ->and(ContactPerson::query()->where('name', 'P. de Vries')->firstOrFail()->email)->toBe('p.devries@example.org')
        ->and($copy?->dataBreachRecords()->pluck('name')->all())->toBe(['Mail naar verkeerde ontvanger'])
        // Everything the export names already exists; nothing may be added twice.
        ->and(Processor::query()->where('organisation_id', $organisationId)->count())->toBe(2)
        ->and(AvgGoal::query()->where('organisation_id', $organisationId)->count())->toBe(1)
        ->and(AvgResponsibleProcessingRecordService::query()->where('organisation_id', $organisationId)->count())->toBe(1)
        ->and(Tag::query()->where('organisation_id', $organisationId)->count())->toBe(1);
});

it('imports its own processor register export back', function (): void {
    $this->asFilamentUser();
    $organisationId = Authentication::organisation()->id;

    $original = AvgProcessorProcessingRecord::factory()->create([
        'organisation_id' => $organisationId,
        'name' => 'Salarisverwerking voor klanten',
        'has_processors' => true,
        'has_security' => true,
        'measures_description' => 'Versleuteling en toegangscontrole.',
        'responsibility_distribution' => 'De klant is verwerkingsverantwoordelijke.',
    ]);
    $original->processors()->attach(
        Processor::factory()->create(['organisation_id' => $organisationId, 'name' => 'Subverwerker X', 'email' => 'x@example.org']),
    );
    $original->stakeholders()->attach(
        Stakeholder::factory()->create(['organisation_id' => $organisationId, 'description' => 'Werknemers van klanten']),
    );
    $original->avgGoals()->attach(
        AvgGoal::factory()->create(
            ['organisation_id' => $organisationId, 'goal' => 'Salarisverwerking', 'avg_goal_legal_base' => 'Overeenkomst'],
        ),
    );
    $original->contactPersons()->attach(ContactPerson::factory()->create(['organisation_id' => $organisationId, 'name' => 'K. Bakker']));

    $page = importWorkbook(
        ImportTarget::AvgProcessorProcessingRecord,
        exportWorkbook(AvgProcessorProcessingRecordExporter::class, [$original]),
    );

    $copy = AvgProcessorProcessingRecord::query()->whereKeyNot($original->id)->where('name', $original->name)->first();

    expect($page->result['imported'])->toBe(1)
        ->and($page->result['issues'])->toBe([])
        ->and($page->result['failures'])->toBe([])
        ->and($copy?->measures_description)->toBe('Versleuteling en toegangscontrole.')
        ->and($copy?->responsibility_distribution)->toBe('De klant is verwerkingsverantwoordelijke.')
        ->and($copy?->processors()->pluck('name')->all())->toBe(['Subverwerker X'])
        ->and($copy?->stakeholders()->pluck('description')->all())->toBe(['Werknemers van klanten'])
        ->and($copy?->avgGoals()->pluck('goal')->all())->toBe(['Salarisverwerking'])
        ->and($copy?->contactPersons()->pluck('name')->all())->toBe(['K. Bakker'])
        ->and(Processor::query()->where('organisation_id', $organisationId)->count())->toBe(1);
});

it('imports its own wpg register export back', function (): void {
    $this->asFilamentUser();
    Config::set('features.wpg', true);
    $organisationId = Authentication::organisation()->id;

    $original = WpgProcessingRecord::factory()->create([
        'organisation_id' => $organisationId,
        'name' => 'Cameratoezicht station',
        'has_processors' => true,
        'has_security' => true,
        'article_17_a' => true,
        'article_18' => true,
        'article_19' => false,
        'article_24' => true,
        'police_justice' => true,
        'explanation_transfer' => 'Doorgifte aan Europol onder verdrag.',
    ]);
    $original->processors()->attach(
        Processor::factory()->create(['organisation_id' => $organisationId, 'name' => 'Beveiligingsbedrijf Z']),
    );
    $original->wpgGoals()->attach(
        WpgGoal::factory()->create(['organisation_id' => $organisationId, 'description' => 'Handhaving openbare orde']),
    );
    $original->contactPersons()->attach(ContactPerson::factory()->create(['organisation_id' => $organisationId, 'name' => 'M. Visser']));

    $page = importWorkbook(ImportTarget::WpgProcessingRecord, exportWorkbook(WpgProcessingRecordExporter::class, [$original]));

    $copy = WpgProcessingRecord::query()->whereKeyNot($original->id)->where('name', $original->name)->first();

    expect($page->result['imported'])->toBe(1)
        ->and($page->result['issues'])->toBe([])
        ->and($page->result['failures'])->toBe([])
        ->and($copy?->article_18)->toBeTrue()
        ->and($copy?->article_19)->toBeFalse()
        ->and($copy?->article_24)->toBeTrue()
        ->and($copy?->police_justice)->toBeTrue()
        ->and($copy?->explanation_transfer)->toBe('Doorgifte aan Europol onder verdrag.')
        ->and($copy?->processors()->pluck('name')->all())->toBe(['Beveiligingsbedrijf Z'])
        ->and($copy?->wpgGoals()->pluck('description')->all())->toBe(['Handhaving openbare orde'])
        ->and($copy?->contactPersons()->pluck('name')->all())->toBe(['M. Visser']);
});

it('imports its own algorithm register export back', function (): void {
    $this->asFilamentUser();
    $organisationId = Authentication::organisation()->id;

    $processing = AvgResponsibleProcessingRecord::factory()->create(['organisation_id' => $organisationId, 'name' => 'Fraudedetectie']);
    $original = AlgorithmRecord::factory()->create([
        'organisation_id' => $organisationId,
        'name' => 'Risicoscore aanvragen',
        'description' => 'Rangschikt aanvragen op kans op fout.',
        'algorithm_theme_id' => AlgorithmTheme::factory()->create(
            ['organisation_id' => $organisationId, 'name' => 'Toezicht', 'enabled' => true],
        )->id,
    ]);
    $original->avgResponsibleProcessingRecords()->attach($processing);

    $page = importWorkbook(ImportTarget::AlgorithmRecord, exportWorkbook(AlgorithmRecordExporter::class, [$original]));

    $copy = AlgorithmRecord::query()->whereKeyNot($original->id)->where('name', $original->name)->first();

    expect($page->result['imported'])->toBe(1)
        ->and($page->result['issues'])->toBe([])
        ->and($page->result['failures'])->toBe([])
        ->and($copy?->description)->toBe('Rangschikt aanvragen op kans op fout.')
        ->and($copy?->algorithmTheme?->name)->toBe('Toezicht')
        ->and($copy?->avgResponsibleProcessingRecords()->pluck('name')->all())->toBe(['Fraudedetectie'])
        ->and(AlgorithmTheme::query()->where('organisation_id', $organisationId)->count())->toBe(1);
});
