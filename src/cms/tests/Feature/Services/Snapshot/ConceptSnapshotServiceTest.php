<?php

declare(strict_types=1);

use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\User;
use App\Services\Snapshot\ConceptSnapshotService;
use App\ValueObjects\CalendarDate;

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

it('starts a new concept once the previous one moved on and the record changed', function (): void {
    $this->be(User::factory()->create());

    $record = AvgResponsibleProcessingRecord::factory()->create();

    /** @var ConceptSnapshotService $conceptSnapshotService */
    $conceptSnapshotService = $this->app->get(ConceptSnapshotService::class);
    $first = $conceptSnapshotService->storeConcept($record);
    $first->state->transitionTo(InReview::class);

    $record->name = 'Gewijzigde naam';
    $record->save();

    $second = $conceptSnapshotService->storeConcept($record);

    expect($record->snapshots()->count())->toBe(2)
        ->and($second)->not->toBeNull()
        ->and($second->id->toString())->not->toBe($first->id->toString())
        ->and($second->state)->toBeInstanceOf(Concept::class)
        ->and($second->version)->toBe($first->version + 1);
});

// The point of the whole thing: pressing save without editing must not put a concept
// next to a version that says exactly the same.
it('writes no concept when the record is identical to the version it would follow', function (): void {
    $this->be(User::factory()->create());

    $record = AvgResponsibleProcessingRecord::factory()->create();

    /** @var ConceptSnapshotService $conceptSnapshotService */
    $conceptSnapshotService = $this->app->get(ConceptSnapshotService::class);
    $first = $conceptSnapshotService->storeConcept($record);
    $first->state->transitionTo(InReview::class);

    $second = $conceptSnapshotService->storeConcept($record->refresh());

    expect($second)->toBeNull()
        ->and($record->snapshots()->count())->toBe(1);
});

// The comparison is against the latest version whatever its state, so an established one
// counts the same as a version still under review.
//
// review_at is filled in up front because establishing a version fills it in when it is
// empty (SnapshotObserver::setReviewAt) — that is a real change to the record, and it
// would make this about that change rather than about the comparison.
it('writes no concept when the record is identical to its established version', function (): void {
    $this->be(User::factory()->create());

    $record = AvgResponsibleProcessingRecord::factory()->create([
        'review_at' => CalendarDate::parse('2028-01-01'),
    ]);

    /** @var ConceptSnapshotService $conceptSnapshotService */
    $conceptSnapshotService = $this->app->get(ConceptSnapshotService::class);
    $first = $conceptSnapshotService->storeConcept($record);
    $first->state->transitionTo(InReview::class);
    $first->state->transitionTo(Established::class);

    $second = $conceptSnapshotService->storeConcept($record->refresh());

    expect($second)->toBeNull()
        ->and($record->snapshots()->count())->toBe(1);
});

// An existing concept is the version compared against, so it stays: it is what the user
// is working on, and saving without changing anything must not throw it away.
it('keeps the existing concept when saving changes nothing', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create();

    /** @var ConceptSnapshotService $conceptSnapshotService */
    $conceptSnapshotService = $this->app->get(ConceptSnapshotService::class);
    $first = $conceptSnapshotService->storeConcept($record);

    $second = $conceptSnapshotService->storeConcept($record->refresh());

    expect($second)->not->toBeNull()
        ->and($second->id->toString())->toBe($first->id->toString())
        ->and($record->snapshots()->count())->toBe(1);
});

// Editing back to what an established version says leaves nothing to submit, so the
// concept that held the intermediate edit goes with it.
it('removes the concept once the record is edited back to the established version', function (): void {
    $this->be(User::factory()->create());

    $record = AvgResponsibleProcessingRecord::factory()->create([
        'name' => 'Oorspronkelijke naam',
        'review_at' => CalendarDate::parse('2028-01-01'),
    ]);

    /** @var ConceptSnapshotService $conceptSnapshotService */
    $conceptSnapshotService = $this->app->get(ConceptSnapshotService::class);
    $established = $conceptSnapshotService->storeConcept($record);
    $established->state->transitionTo(InReview::class);
    $established->state->transitionTo(Established::class);

    $record->name = 'Tussentijdse naam';
    $record->save();
    $concept = $conceptSnapshotService->storeConcept($record->refresh());
    expect($concept)->not->toBeNull();

    $record->name = 'Oorspronkelijke naam';
    $record->save();

    expect($conceptSnapshotService->storeConcept($record->refresh()))->toBeNull()
        ->and($record->snapshots()->count())->toBe(1);
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
