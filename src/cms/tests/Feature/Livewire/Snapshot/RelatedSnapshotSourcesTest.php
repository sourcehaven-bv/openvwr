<?php

declare(strict_types=1);

use App\Livewire\Snapshot\RelatedSnapshotSources;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Processor;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use Tests\Helpers\Model\OrganisationTestHelper;

it('can load the table', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create();
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->create();

    // no snapshot
    $processor = Processor::factory()
        ->recycle($organisation)
        ->create();
    $relatedSnapshotSource = RelatedSnapshotSource::factory()
        ->for($snapshot)
        ->create([
            'snapshot_id' => $snapshot->id,
            'snapshot_source_id' => $processor->id,
            'snapshot_source_type' => Processor::class,
        ]);

    // no snapshot and deleted
    $deletedProcessor = Processor::factory()
        ->recycle($organisation)
        ->create(['deleted_at' => fake()->dateTime()]);
    $deletedRelatedSnapshotSource = RelatedSnapshotSource::factory()
        ->for($snapshot)
        ->create([
            'snapshot_id' => $snapshot->id,
            'snapshot_source_id' => $deletedProcessor->id,
            'snapshot_source_type' => Processor::class,
        ]);

    // established
    $establishedProcessor = Processor::factory()
        ->recycle($organisation)
        ->create();
    $establishedProcessorSnapshotSource = RelatedSnapshotSource::factory()
        ->for($snapshot)
        ->create([
            'snapshot_id' => $snapshot->id,
            'snapshot_source_id' => $establishedProcessor->id,
            'snapshot_source_type' => Processor::class,
        ]);
    Snapshot::factory()
        ->for($establishedProcessor, 'snapshotSource')
        ->recycle($organisation)
        ->create(['state' => Established::class]);

    // Approved
    $approvedProcessor = Processor::factory()
        ->recycle($organisation)
        ->create();
    $approvedProcessorSnapshotSource = RelatedSnapshotSource::factory()
        ->for($snapshot)
        ->create([
            'snapshot_id' => $snapshot->id,
            'snapshot_source_id' => $approvedProcessor->id,
            'snapshot_source_type' => Processor::class,
        ]);
    Snapshot::factory()
        ->for($approvedProcessor, 'snapshotSource')
        ->recycle($organisation)
        ->create(['state' => Approved::class]);

    // InReview
    $inReviewProcessor = Processor::factory()
        ->recycle($organisation)
        ->create();
    $inReviewProcessorSnapshotSource = RelatedSnapshotSource::factory()
        ->for($snapshot)
        ->create([
            'snapshot_id' => $snapshot->id,
            'snapshot_source_id' => $inReviewProcessor->id,
            'snapshot_source_type' => Processor::class,
        ]);
    Snapshot::factory()
        ->for($inReviewProcessor, 'snapshotSource')
        ->recycle($organisation)
        ->create(['state' => InReview::class]);

    // Obsolete
    $obsoleteProcessor = Processor::factory()
        ->recycle($organisation)
        ->create();
    $obsoleteProcessorSnapshotSource = RelatedSnapshotSource::factory()
        ->for($snapshot)
        ->create([
            'snapshot_id' => $snapshot->id,
            'snapshot_source_id' => $obsoleteProcessor->id,
            'snapshot_source_type' => Processor::class,
        ]);
    Snapshot::factory()
        ->for($obsoleteProcessor, 'snapshotSource')
        ->recycle($organisation)
        ->create(['state' => Obsolete::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(RelatedSnapshotSources::class, [
            'snapshot' => $snapshot,
        ])
        ->assertCanSeeTableRecords([
            $relatedSnapshotSource,
            $deletedRelatedSnapshotSource,
            $establishedProcessorSnapshotSource,
            $approvedProcessorSnapshotSource,
            $inReviewProcessorSnapshotSource,
            $obsoleteProcessorSnapshotSource,
        ]);
});

it('renders rows whose source has been hard-deleted', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create();
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->create();

    // The polymorphic target has no foreign key, so hard-deleting the source
    // leaves an orphan row. The table must still render: no record link, no
    // view action, and a placeholder instead of a display name.
    $processor = Processor::factory()
        ->recycle($organisation)
        ->create();
    $orphan = RelatedSnapshotSource::factory()
        ->for($snapshot)
        ->create([
            'snapshot_id' => $snapshot->id,
            'snapshot_source_id' => $processor->id,
            'snapshot_source_type' => Processor::class,
        ]);
    Processor::query()->whereKey($processor->id)->forceDelete();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(RelatedSnapshotSources::class, [
            'snapshot' => $snapshot,
        ])
        ->assertOk()
        ->assertCanSeeTableRecords([$orphan])
        ->assertTableActionHidden('view', $orphan);
});
