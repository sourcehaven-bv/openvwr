<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Exports;

use App\Filament\Exports\AvgResponsibleProcessingRecordExporter;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Organisation;
use Filament\Actions\Exports\Models\Export;
use Filament\Facades\Filament;
use Tests\Helpers\Model\OrganisationTestHelper;

use function array_keys;
use function expect;
use function it;
use function sprintf;
use function str_replace;

/**
 * @return array<string>
 */
function columnLabels(): array
{
    $labels = [];

    foreach (AvgResponsibleProcessingRecordExporter::getColumns() as $column) {
        $labels[] = $column->getLabel();
    }

    return $labels;
}

function documentTypeAlias(DocumentType $documentType): string
{
    return sprintf('document_type_%s_count', str_replace('-', '', $documentType->id->toString()));
}

it('adds a column per enabled document type of the current organisation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);
    DocumentType::factory()->for($organisation)->create(['name' => 'DPIA']);

    expect(columnLabels())
        ->toContain('Documenten')
        ->toContain('VWO')
        ->toContain('DPIA');
});

it('leaves out document types of another organisation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);
    DocumentType::factory()->for(OrganisationTestHelper::create())->create(['name' => 'Andermans type']);

    expect(columnLabels())
        ->toContain('VWO')
        ->not->toContain('Andermans type');
});

it('leaves out disabled document types', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);
    DocumentType::factory()->for($organisation)->create(['name' => 'Uitgezet', 'enabled' => false]);

    expect(columnLabels())
        ->toContain('VWO')
        ->not->toContain('Uitgezet');
});

it('counts the documents of each type separately, and all of them in the total', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    $vwo = DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);
    $dpia = DocumentType::factory()->for($organisation)->create(['name' => 'DPIA']);

    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create();

    $record->documents()->attach([
        Document::factory()->for($organisation)->for($vwo, 'documentType')->create()->id->toString(),
        Document::factory()->for($organisation)->for($dpia, 'documentType')->create()->id->toString(),
        Document::factory()->for($organisation)->for($dpia, 'documentType')->create()->id->toString(),
    ]);

    $query = AvgResponsibleProcessingRecord::query()->whereKey($record->id);

    foreach (AvgResponsibleProcessingRecordExporter::getColumns() as $column) {
        $column->applyRelationshipAggregates($query);
    }

    $result = $query->firstOrFail();

    expect($result->getAttribute('documents_count'))->toBe(3)
        ->and($result->getAttribute(documentTypeAlias($vwo)))->toBe(1)
        ->and($result->getAttribute(documentTypeAlias($dpia)))->toBe(2);
});

it('reports zero for a type the record has no document of', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    $vwo = DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);

    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create();

    $query = AvgResponsibleProcessingRecord::query()->whereKey($record->id);

    foreach (AvgResponsibleProcessingRecordExporter::getColumns() as $column) {
        $column->applyRelationshipAggregates($query);
    }

    $result = $query->firstOrFail();

    expect($result->getAttribute(documentTypeAlias($vwo)))->toBe(0);
});

it('still counts a document without a type in the total', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create();

    $document = Document::factory()->for($organisation)->create();
    $document->documentType()->disassociate();
    $document->save();

    $record->documents()->attach($document->id->toString());

    $query = AvgResponsibleProcessingRecord::query()->whereKey($record->id);

    foreach (AvgResponsibleProcessingRecordExporter::getColumns() as $column) {
        $column->applyRelationshipAggregates($query);
    }

    expect($query->firstOrFail()->getAttribute('documents_count'))->toBe(1);
});

/**
 * The export job runs in a worker, where Filament has no tenant. It rebuilds the
 * columns to write the rows, so if it cannot restore the organisation the rows
 * come out one cell short of the header for every document type.
 */
it('rebuilds the same columns in a job that has no tenant', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);

    $columnMap = [];

    foreach (AvgResponsibleProcessingRecordExporter::getColumns() as $column) {
        $columnMap[$column->getName()] = $column->getLabel();
    }

    // What the worker sees: a user, but no tenant resolved from the URL.
    Filament::setTenant(null);

    $export = new Export();
    $export->exporter = AvgResponsibleProcessingRecordExporter::class;

    $exporter = new AvgResponsibleProcessingRecordExporter(
        $export,
        $columnMap,
        ['organisation_id' => $organisation->id->toString()],
    );

    expect(array_keys($exporter->getCachedColumns()))
        ->toBe(array_keys($columnMap));
});

/**
 * An export queued before this change carries no organisation in its options, and
 * its job would still be picked up after the deploy. It has to fall back to the
 * total rather than fail, which is also what the header of that export promised.
 */
it('falls back to the total for an export without an organisation in its options', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);

    Filament::setTenant(null);

    $export = new Export();
    $export->exporter = AvgResponsibleProcessingRecordExporter::class;

    $exporter = new AvgResponsibleProcessingRecordExporter($export, [], []);

    $labels = [];

    foreach ($exporter->getCachedColumns() as $column) {
        $labels[] = $column->getLabel();
    }

    expect($labels)
        ->toContain('Documenten')
        ->not->toContain('VWO');
});

it('leaves out the per-type columns when there is no tenant at all', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);

    Filament::setTenant(null);

    expect(columnLabels())
        ->toContain('Documenten')
        ->not->toContain('VWO');
});

it('restores the absence of a tenant after building the columns in a job', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    Filament::setTenant(null);

    $export = new Export();
    $export->exporter = AvgResponsibleProcessingRecordExporter::class;

    (new AvgResponsibleProcessingRecordExporter(
        $export,
        [],
        ['organisation_id' => $organisation->id->toString()],
    ))->getCachedColumns();

    expect(Filament::getTenant())->toBeNull();
});

it('keeps the columns of the active tenant when one is already set', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);

    $export = new Export();
    $export->exporter = AvgResponsibleProcessingRecordExporter::class;

    // An options payload pointing at a different organisation must not override
    // the tenant of the request that is running.
    $exporter = new AvgResponsibleProcessingRecordExporter(
        $export,
        [],
        ['organisation_id' => OrganisationTestHelper::create()->id->toString()],
    );

    $labels = [];

    foreach ($exporter->getCachedColumns() as $column) {
        $labels[] = $column->getLabel();
    }

    expect($labels)->toContain('VWO')
        ->and(Filament::getTenant())->toBeInstanceOf(Organisation::class);
});

it('gives every document type its own column', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    $vwo = DocumentType::factory()->for($organisation)->create(['name' => 'VWO']);
    $dpia = DocumentType::factory()->for($organisation)->create(['name' => 'DPIA']);

    $names = [];

    foreach (AvgResponsibleProcessingRecordExporter::getColumns() as $column) {
        $names[] = $column->getName();
    }

    expect($names)
        ->toContain(documentTypeAlias($vwo))
        ->toContain(documentTypeAlias($dpia));
});
