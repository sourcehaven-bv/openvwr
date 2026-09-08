<?php

declare(strict_types=1);

use App\Enums\Import\ImportTarget;
use App\Facades\Authentication;
use App\Filament\Exports\AvgResponsibleProcessingRecordExporter;
use App\Filament\Exports\DataBreachRecordExporter;
use App\Filament\Exports\Exporter;
use App\Filament\Pages\ImportMapping;
use App\Import\Mapping\DryRunner;
use App\Import\Mapping\EditableMapping;
use App\Import\Mapping\MappedRecordWriter;
use App\Import\Mapping\MappingAnalyser;
use App\Import\Mapping\MappingProfileRepository;
use App\Import\Mapping\SheetReader;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\ContactPerson;
use App\Models\DataBreachRecord;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\Stakeholder;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Model;
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
        'has_processors' => true,
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
        $original->processors()->attach(Processor::factory()->create(['organisation_id' => $organisationId, 'name' => $name]));
    }

    $original->receivers()->attach(Receiver::factory()->create(['organisation_id' => $organisationId, 'description' => 'Belastingdienst']));
    $original->stakeholders()->attach(
        Stakeholder::factory()->create(['organisation_id' => $organisationId, 'description' => 'Medewerkers']),
    );
    $original->avgGoals()->attach(AvgGoal::factory()->create(['organisation_id' => $organisationId, 'goal' => 'Uitbetalen van salaris']));
    $original->contactPersons()->attach(ContactPerson::factory()->create(['organisation_id' => $organisationId, 'name' => 'P. de Vries']));

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
        ->and($copy?->outside_eu)->toBeFalse()
        ->and($copy?->measures_description)->toBe('Toegang op basis van rol; logging van inzage.')
        ->and($copy?->avgResponsibleProcessingRecordService?->name)->toBe('HR')
        ->and($copy?->processors()->pluck('name')->sort()->values()->all())->toBe(['Firma A', 'Firma B'])
        ->and($copy?->receivers()->pluck('description')->all())->toBe(['Belastingdienst'])
        ->and($copy?->stakeholders()->pluck('description')->all())->toBe(['Medewerkers'])
        ->and($copy?->avgGoals()->pluck('goal')->all())->toBe(['Uitbetalen van salaris'])
        ->and($copy?->contactPersons()->pluck('name')->all())->toBe(['P. de Vries'])
        // Everything the export names already exists; nothing may be added twice.
        ->and(Processor::query()->where('organisation_id', $organisationId)->count())->toBe(2)
        ->and(AvgGoal::query()->where('organisation_id', $organisationId)->count())->toBe(1)
        ->and(AvgResponsibleProcessingRecordService::query()->where('organisation_id', $organisationId)->count())->toBe(1);
});
