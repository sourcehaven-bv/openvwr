<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Exports;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use App\Filament\Exports\AvgResponsibleProcessingRecordExporter;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Organisation;
use Filament\Actions\Exports\Jobs\ExportCsv;
use Filament\Actions\Exports\Models\Export;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\Model\OrganisationTestHelper;

use function array_combine;
use function array_values;
use function expect;
use function it;
use function str_getcsv;
use function str_pad;
use function trim;

use const STR_PAD_LEFT;

/**
 * Runs the export the way the queue does: build the header in a request, then let
 * the job write the rows with no tenant in place.
 *
 * @return array<string, string>
 */
function runExport(Organisation $organisation, AvgResponsibleProcessingRecord $record): array
{
    $columnMap = [];

    foreach (AvgResponsibleProcessingRecordExporter::getColumns() as $column) {
        $columnMap[$column->getName()] = (string) $column->getLabel();
    }

    $export = new Export();
    $export->exporter = AvgResponsibleProcessingRecordExporter::class;
    $export->total_rows = 1;
    $export->file_disk = 'filament';
    $export->file_name = 'test-export';
    $export->user()->associate($organisation->users->first());
    $export->save();

    $query = AvgResponsibleProcessingRecordExporter::modifyQuery(
        AvgResponsibleProcessingRecord::query(),
    );

    $options = ['organisation_id' => $organisation->getKey()->toString()];

    $job = new ExportCsv(
        export: $export,
        query: EloquentSerializeFacade::serialize($query),
        records: [$record->getKey()->toString()],
        page: 1,
        columnMap: $columnMap,
        options: $options,
    );

    // A queue worker has no tenant resolved from the URL.
    Filament::setTenant(null);

    $job->handle();

    $path = $export->getFileDirectory() . '/' . str_pad('1', 16, '0', STR_PAD_LEFT) . '.csv';
    $csv = Storage::disk('filament')->get($path);

    expect($csv)->not->toBeNull();

    $row = str_getcsv(trim((string) $csv));

    return array_combine(array_values($columnMap), $row);
}

it('writes a count per document type into the exported row', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    $vwo = DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);
    $dpia = DocumentType::factory()->for($organisation)->create(['name' => 'DPIA']);
    DocumentType::factory()->for($organisation)->create(['name' => 'Beveiligingsbeleid']);

    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create();

    $record->documents()->attach([
        Document::factory()->for($organisation)->for($vwo, 'documentType')->create()->getKey()->toString(),
        Document::factory()->for($organisation)->for($dpia, 'documentType')->create()->getKey()->toString(),
        Document::factory()->for($organisation)->for($dpia, 'documentType')->create()->getKey()->toString(),
    ]);

    $row = runExport($organisation, $record);

    expect($row['Documenten'])->toBe('3')
        ->and($row['VWO'])->toBe('1')
        ->and($row['DPIA'])->toBe('2')
        ->and($row['Beveiligingsbeleid'])->toBe('0');
});

it('answers which processing lacks a vwo', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    $dpia = DocumentType::factory()->for($organisation)->create(['name' => 'DPIA']);
    DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);

    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create();

    $record->documents()->attach(
        Document::factory()->for($organisation)->for($dpia, 'documentType')->create()->getKey()->toString(),
    );

    $row = runExport($organisation, $record);

    // The whole point: one document attached, but none of them a VWO.
    expect($row['Documenten'])->toBe('1')
        ->and($row['VWO'])->toBe('0');
});
