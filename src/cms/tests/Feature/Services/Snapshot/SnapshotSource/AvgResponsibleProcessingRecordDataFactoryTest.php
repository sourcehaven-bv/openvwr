<?php

declare(strict_types=1);

use App\Enums\CoreEntityDataCollectionSource;
use App\Enums\SitemapType;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\EntityNumber;
use App\Models\Snapshot;
use App\Services\Snapshot\SnapshotSource\AvgResponsibleProcessingRecordDataFactory;

it('can generate private markdown', function (): void {
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create([
            'data_collection_source' => CoreEntityDataCollectionSource::SECONDARY,
            'name' => 'laboriosam',
            'review_at' => '2001-07-21',
            'entity_number_id' => EntityNumber::factory()->state([
                'number' => '7cb7c7db-6e97-3391-8153-477df1e8fde8',
            ]),
            'import_id' => null,
            'created_at' => '2024-04-18 12:18',
            'updated_at' => '2024-04-18 12:18',
            'responsibility_distribution' => 'Et dicta illo suscipit sint sunt accusamus.',
            'has_security' => true,
            'has_pseudonymization' => true,
            'pseudonymization' => 'Aut porro et nulla.',
            'decision_making' => false,
            'logic' => null,
            'importance_consequences' => null,
            'outside_eu' => false,
            'geb_dpia_executed' => 'yes',
            // The criteria print unconditionally now, so pin them for a stable
            // snapshot instead of letting the factory randomise them.
            'geb_dpia_automated' => true,
            'geb_dpia_large_scale_processing' => false,
            'geb_dpia_large_scale_monitoring' => false,
            'geb_dpia_list_required' => true,
            'geb_dpia_criteria_wp248' => false,
            'geb_dpia_high_risk_freedoms' => false,
            'public_from' => '2012-05-24',
            'avg_responsible_processing_record_service_id' => AvgResponsibleProcessingRecordService::factory()->state([
                'name' => 'In modi dolore aspernatur nobis ullam magni minus ipsum.',
                'enabled' => true,
            ]),
        ]);
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create();

    $avgResponsibleProcessingRecordDataFactory = new AvgResponsibleProcessingRecordDataFactory();
    expect($avgResponsibleProcessingRecordDataFactory->generatePrivateMarkdown($snapshot))
        ->toMatchSnapshot();
});

it('can generate public frontmatter', function (): void {
    $name = fake()->uuid();
    $number = fake()->uuid();

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create([
            'name' => $name,
            'entity_number_id' => EntityNumber::factory()->state([
                'number' => $number,
            ]),
        ]);
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create();

    $avgResponsibleProcessingRecordDataFactory = new AvgResponsibleProcessingRecordDataFactory();
    expect($avgResponsibleProcessingRecordDataFactory->generatePublicFrontmatter($snapshot))
        ->toBe([
            'title' => $name,
            'type' => SitemapType::PROCESSING_RECORD->value,
            'record' => [
                'reference' => $number,
                'title' => $name,
                'description' => '',
            ],
        ]);
});

it('can generate public markdown', function (): void {
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create([
            'name' => 'e8ef4656-8534-31f7-b4d0-7852660f5c49',
            'decision_making' => true,
            'outside_eu' => false,
        ]);
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create();

    $avgResponsibleProcessingRecordDataFactory = new AvgResponsibleProcessingRecordDataFactory();
    expect($avgResponsibleProcessingRecordDataFactory->generatePublicMarkdown($snapshot))
        ->toMatchSnapshot();
});
