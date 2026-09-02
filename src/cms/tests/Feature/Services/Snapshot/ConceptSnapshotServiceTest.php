<?php

declare(strict_types=1);

use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\InReview;
use App\Models\User;
use App\Services\Snapshot\ConceptSnapshotService;

it('creates a concept snapshot for a record that has none', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create();

    /** @var ConceptSnapshotService $conceptSnapshotService */
    $conceptSnapshotService = $this->app->get(ConceptSnapshotService::class);
    $snapshot = $conceptSnapshotService->storeConcept($record);

    expect($record->snapshots()->count())->toBe(1)
        ->and($snapshot->state)->toBeInstanceOf(Concept::class)
        ->and($snapshot->version)->toBe(1)
        ->and($snapshot->snapshotData)->not->toBeNull();
});

it('updates the existing concept instead of adding a second one', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create();

    /** @var ConceptSnapshotService $conceptSnapshotService */
    $conceptSnapshotService = $this->app->get(ConceptSnapshotService::class);
    $first = $conceptSnapshotService->storeConcept($record);

    $record->name = 'Gewijzigde naam';
    $record->save();

    $second = $conceptSnapshotService->storeConcept($record);

    expect($record->snapshots()->count())->toBe(1)
        ->and($second->id->toString())->toBe($first->id->toString())
        ->and($second->name)->toBe('Gewijzigde naam')
        ->and($second->version)->toBe($first->version);
});

it('leaves the concept with a single set of data after a refresh', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create();

    /** @var ConceptSnapshotService $conceptSnapshotService */
    $conceptSnapshotService = $this->app->get(ConceptSnapshotService::class);
    $conceptSnapshotService->storeConcept($record);
    $snapshot = $conceptSnapshotService->storeConcept($record);

    expect($snapshot->snapshotData()->count())->toBe(1);
});

it('starts a new concept once the previous one moved on', function (): void {
    $this->be(User::factory()->create());

    $record = AvgResponsibleProcessingRecord::factory()->create();

    /** @var ConceptSnapshotService $conceptSnapshotService */
    $conceptSnapshotService = $this->app->get(ConceptSnapshotService::class);
    $first = $conceptSnapshotService->storeConcept($record);
    $first->state->transitionTo(InReview::class);

    $second = $conceptSnapshotService->storeConcept($record);

    expect($record->snapshots()->count())->toBe(2)
        ->and($second->id->toString())->not->toBe($first->id->toString())
        ->and($second->state)->toBeInstanceOf(Concept::class)
        ->and($second->version)->toBe($first->version + 1);
});

it('does not touch snapshots of other records', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create();
    $other = AvgResponsibleProcessingRecord::factory()->create();

    Snapshot::factory()->create([
        'snapshot_source_id' => $other->id,
        'snapshot_source_type' => AvgResponsibleProcessingRecord::class,
        'state' => Concept::class,
    ]);

    /** @var ConceptSnapshotService $conceptSnapshotService */
    $conceptSnapshotService = $this->app->get(ConceptSnapshotService::class);
    $conceptSnapshotService->storeConcept($record);

    expect($record->snapshots()->count())->toBe(1)
        ->and($other->snapshots()->count())->toBe(1);
});
