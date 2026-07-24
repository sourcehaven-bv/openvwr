<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners\Media;

use App\Events\StaticWebsite\BuildEvent;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use App\Models\User;
use Illuminate\Support\Facades\Event;

use function expect;
use function fake;
use function it;

it('does not dispatch the build-event when snapshot is created', function (): void {
    Event::fake(BuildEvent::class);

    Snapshot::factory()->create();

    Event::assertNotDispatched(BuildEvent::class);
});

it(
    'dispatches the build-event when needed if snapshot is updated',
    function (string $oldState, string $newState, bool $expectedEvent): void {
        $snapshot = Snapshot::factory()
            ->createQuietly([
                'state' => $oldState,
            ]);

        Event::fake(BuildEvent::class);

        $snapshot->state = $newState;
        $snapshot->save();

        Event::assertDispatchedTimes(BuildEvent::class, (int) $expectedEvent);
    },
)->with([
    'old in_review, new approved' => [InReview::class, Approved::class, false],
    'old approved, new established' => [Approved::class, Established::class, true],
    'old in_review, new established' => [InReview::class, Established::class, true],
    'old in_review, new obsolete' => [InReview::class, Obsolete::class, false],
    'old approved, new obsolete' => [Approved::class, Obsolete::class, false],
    'old established, new obsolete' => [Established::class, Obsolete::class, true],
]);

it('does not dispatch the build-event if snapshot is deleted', function (): void {
    $snapshot = Snapshot::factory()->createQuietly();

    Event::fake(BuildEvent::class);

    $snapshot->delete();

    Event::assertNotDispatched(BuildEvent::class);
});

it('will set the review_at if snapshotSource is reviewable and not set', function (): void {
    $this->be(User::factory()->create());

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create([
            'review_at' => null,
        ]);
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => Approved::class,
        ]);
    $snapshot->state->transitionTo(Established::class);

    $avgResponsibleProcessingRecord->refresh();

    expect($avgResponsibleProcessingRecord->review_at)
        ->not()->toBeNull();
});

it('will not set the review_at if snapshotSource is reviewable but set', function (): void {
    $this->be(User::factory()->create());

    $reviewAt = fake()->calendarDate();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create([
            'review_at' => $reviewAt,
        ]);
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => Approved::class,
        ]);
    $snapshot->state->transitionTo(Established::class);

    $avgResponsibleProcessingRecord->refresh();

    expect($reviewAt->equalTo($avgResponsibleProcessingRecord->review_at))
        ->toBeTrue();
});
