<?php

declare(strict_types=1);

use App\Enums\Dpia\RiskLevel;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaRecord;
use Tests\Helpers\Model\OrganisationTestHelper;

// Artikel 36 AVG: a high residual risk that cannot be brought down means the
// AP has to be consulted before the processing starts.
it('requires AP consultation when a measure leaves a high residual risk', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'residual_level' => RiskLevel::HIGH,
    ]);

    expect($dpiaRecord->fresh()->requiresApConsultation())->toBeTrue();
});

it('does not require AP consultation when residual risks are acceptable', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'residual_level' => RiskLevel::LOW,
    ]);

    expect($dpiaRecord->fresh()->requiresApConsultation())->toBeFalse();
});

it('reports the highest residual risk level', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();

    foreach ([RiskLevel::LOW, RiskLevel::MEDIUM, RiskLevel::LOW] as $level) {
        DpiaMeasure::factory()->recycle($organisation)->create([
            'dpia_record_id' => $dpiaRecord->id,
            'residual_level' => $level,
        ]);
    }

    expect($dpiaRecord->fresh()->highestResidualRiskLevel())->toBe(RiskLevel::MEDIUM);
});

it('has no residual risk level without measures', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();

    expect($dpiaRecord->highestResidualRiskLevel())->toBeNull();
});

// One DPIA may cover a series of comparable processing operations
// (artikel 35, eerste lid, AVG and overweging 92).
it('can cover more than one verwerking', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $processingRecords = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->count(2)
        ->create();

    $dpiaRecord->avgResponsibleProcessingRecords()->sync(
        $processingRecords->pluck('id')->map(static fn ($id): string => $id->toString()),
    );

    expect($dpiaRecord->fresh()->avgResponsibleProcessingRecords)->toHaveCount(2);
});

it('is reachable from the verwerking it covers', function (): void {
    $organisation = OrganisationTestHelper::create();

    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();

    $dpiaRecord->avgResponsibleProcessingRecords()->sync([$processingRecord->id->toString()]);

    expect($processingRecord->fresh()->dpiaRecords->pluck('id')->map->toString()->all())
        ->toBe([$dpiaRecord->id->toString()]);
});
