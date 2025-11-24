<?php

declare(strict_types=1);

use App\Enums\CoreEntityDataCollectionSource;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgProcessorProcessingRecordService;
use App\Models\EntityNumber;
use App\Models\Snapshot;
use App\Services\Snapshot\SnapshotSource\AvgProcessorProcessingRecordDataFactory;

it('can generate private markdown', function (): void {
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->create([
            'entity_number_id' => EntityNumber::factory()->state([
                'number' => '7cb7c7db-6e97-3391-8153-477df1e8fde8',
            ]),
            'name' => '8a354023-7c12-3555-92bf-3895cf6c5704',
            'decision_making' => true,
            'outside_eu' => false,
            'import_id' => null,
            'data_collection_source' => CoreEntityDataCollectionSource::SECONDARY,
            'review_at' => '2001-07-21',
            'logic' => 'Et dicta illo suscipit sint sunt accusamus.',
            'importance_consequences' => 'Et dicta illo suscipit sint sunt accusamus.',
            'public_from' => '2012-05-24',
            'has_pseudonymization' => true,
            'pseudonymization' => 'Aut porro et nulla.',
            'geb_pia' => true,
            'has_security' => true,
            'avg_processor_processing_record_service_id' => AvgProcessorProcessingRecordService::factory()->state([
                'name' => 'In modi dolore aspernatur nobis ullam magni minus ipsum.',
                'enabled' => true,
            ]),
        ]);
    $snapshot = Snapshot::factory()
        ->for($avgProcessorProcessingRecord, 'snapshotSource')
        ->create();

    $avgProcessorProcessingRecordDataFactory = new AvgProcessorProcessingRecordDataFactory();
    expect($avgProcessorProcessingRecordDataFactory->generatePrivateMarkdown($snapshot))
        ->toMatchSnapshot();
});

it('can generate public frontmatter', function (): void {
    $snapshot = Snapshot::factory()
        ->create();

    $avgProcessorProcessingRecordDataFactory = new AvgProcessorProcessingRecordDataFactory();
    expect($avgProcessorProcessingRecordDataFactory->generatePublicFrontmatter($snapshot))
        ->toBe([]);
});

it('can generate public markdown', function (): void {
    $snapshot = Snapshot::factory()
        ->create();

    $avgProcessorProcessingRecordDataFactory = new AvgProcessorProcessingRecordDataFactory();
    expect($avgProcessorProcessingRecordDataFactory->generatePublicMarkdown($snapshot))
        ->toBeNull();
});
