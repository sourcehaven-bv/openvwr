<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\SnapshotTransition;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use App\Models\States\SnapshotState;
use App\Models\User;
use App\Notifications\SnapshotApprovalSignRequest;
use App\Services\Snapshot\SnapshotStateTransitionService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('makes previous snapshots (only of the same state) obsolete', function (): void {
    Event::fake();

    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();
    $this->be($user);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()->create();
    $snapshot1 = Snapshot::factory()->create([
        'snapshot_source_type' => AvgResponsibleProcessingRecord::class,
        'snapshot_source_id' => $avgResponsibleProcessingRecord->id,
        'state' => Established::class,
    ]);
    $snapshot2 = Snapshot::factory()->create([
        'snapshot_source_type' => AvgResponsibleProcessingRecord::class,
        'snapshot_source_id' => $avgResponsibleProcessingRecord->id,
        'state' => Approved::class,
    ]);
    $snapshot3 = Snapshot::factory()->create([
        'snapshot_source_type' => AvgResponsibleProcessingRecord::class,
        'snapshot_source_id' => $avgResponsibleProcessingRecord->id,
        'state' => Obsolete::class,
    ]);
    $snapshot4 = Snapshot::factory()->create([
        'snapshot_source_type' => AvgResponsibleProcessingRecord::class,
        'snapshot_source_id' => $avgResponsibleProcessingRecord->id,
        'state' => InReview::class,
    ]);

    /** @var SnapshotStateTransitionService $snapshotStateTransitionService */
    $snapshotStateTransitionService = $this->app->get(SnapshotStateTransitionService::class);
    /** @var SnapshotState $approved */
    $approved = SnapshotState::make(Approved::class, $snapshot1);
    $snapshotStateTransitionService->transitionToSnapshotState($snapshot4, $approved);

    expect($snapshot1->refresh()->state)
        ->toBeInstanceOf(Established::class)
        ->and($snapshot2->refresh()->state)
        ->toBeInstanceOf(Obsolete::class)
        ->and($snapshot3->refresh()->state)
        ->toBeInstanceOf(Obsolete::class)
        ->and($snapshot4->refresh()->state)
        ->toBeInstanceOf(Approved::class);
});

it('does not obsolete snapshots which are already obsolete', function (): void {
    Event::fake();

    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();
    $this->be($user);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()->create();
    $snapshot1 = Snapshot::factory()->create([
        'snapshot_source_type' => AvgResponsibleProcessingRecord::class,
        'snapshot_source_id' => $avgResponsibleProcessingRecord->id,
        'state' => Established::class,
    ]);
    $snapshot2 = Snapshot::factory()->create([
        'snapshot_source_type' => AvgResponsibleProcessingRecord::class,
        'snapshot_source_id' => $avgResponsibleProcessingRecord->id,
        'state' => Obsolete::class,
    ]);

    /** @var SnapshotStateTransitionService $snapshotStateTransitionService */
    $snapshotStateTransitionService = $this->app->get(SnapshotStateTransitionService::class);
    /** @var SnapshotState $snapshotState */
    $snapshotState = SnapshotState::make(Obsolete::class, $snapshot1);
    $snapshotStateTransitionService->transitionToSnapshotState($snapshot1, $snapshotState);

    expect($snapshot1->refresh()->state)
        ->toBeInstanceOf(Obsolete::class)
        ->and($snapshot2->refresh()->state)
        ->toBeInstanceOf(Obsolete::class);
});

it('sets replaced at attribute when snapshot is obsoleted', function (): void {
    Event::fake();

    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();
    $this->be($user);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => Established::class,
            'replaced_at' => null,
        ]);

    /** @var SnapshotState $snapshotState */
    $snapshotState = SnapshotState::make(Obsolete::class, $snapshot);

    $snapshotStateTransitionService = $this->app->get(SnapshotStateTransitionService::class);
    $snapshotStateTransitionService->transitionToSnapshotState($snapshot, $snapshotState);
    expect($snapshot->refresh()->replaced_at)
        ->not()->toBeNull();
});


it('sets established at attribute when snapshot is established', function (): void {
    Event::fake();
    CarbonImmutable::setTestNow('2026-07-23 12:00:00');

    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();
    $this->be($user);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => Approved::class,
            'established_at' => null,
        ]);

    /** @var SnapshotState $snapshotState */
    $snapshotState = SnapshotState::make(Established::class, $snapshot);

    $snapshotStateTransitionService = $this->app->get(SnapshotStateTransitionService::class);
    $snapshotStateTransitionService->transitionToSnapshotState($snapshot, $snapshotState);
    expect($snapshot->refresh()->established_at?->toDateTimeString())
        ->toBe('2026-07-23 12:00:00');

    CarbonImmutable::setTestNow();
});


it('can transition even if snapshotSource is deleted', function (): void {
    Event::fake();

    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();
    $this->be($user);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'deleted_at' => fake()->dateTime(),
        ]);
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->create([
            'state' => Established::class,
            'replaced_at' => null,
        ]);

    /** @var SnapshotState $snapshotState */
    $snapshotState = SnapshotState::make(Obsolete::class, $snapshot);

    $snapshotStateTransitionService = $this->app->get(SnapshotStateTransitionService::class);
    $snapshotStateTransitionService->transitionToSnapshotState($snapshot, $snapshotState);
    expect($snapshot->refresh()->replaced_at)
        ->not()->toBeNull();
});

it('establishes straight from review without recording an approved step', function (): void {
    Event::fake();

    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();
    $this->be($user);
    CarbonImmutable::setTestNow('2026-07-23 12:00:00');

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => InReview::class,
            'established_at' => null,
        ]);

    /** @var SnapshotState $established */
    $established = SnapshotState::make(Established::class, $snapshot);

    $snapshotStateTransitionService = $this->app->get(SnapshotStateTransitionService::class);
    $snapshotStateTransitionService->transitionToSnapshotState($snapshot, $established);

    // Reached established and stamped established_at...
    expect($snapshot->refresh()->state)->toBeInstanceOf(Established::class)
        ->and($snapshot->established_at?->toDateTimeString())->toBe('2026-07-23 12:00:00');

    // ...but the bypassed approval step leaves no history row: only established.
    $recordedStates = SnapshotTransition::where('snapshot_id', $snapshot->id)
        ->get()
        ->map(static fn (SnapshotTransition $transition): string => $transition->state::$name)
        ->all();
    expect($recordedStates)
        ->toContain(Established::$name)
        ->not->toContain(Approved::$name);

    CarbonImmutable::setTestNow();
});

it('does not notify approvers when establishing straight from review', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();
    $this->be($user);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => InReview::class,
        ]);
    $approver = User::factory()
        ->hasAttached($organisation)
        ->create();
    SnapshotApproval::factory()
        ->for($snapshot)
        ->for($approver, 'assignedTo')
        ->create();

    /** @var SnapshotState $established */
    $established = SnapshotState::make(Established::class, $snapshot);

    // Fake only from here so the approval-created observer notification (part of
    // fixture setup) is not counted: we assert purely about the transition.
    Notification::fake();

    $snapshotStateTransitionService = $this->app->get(SnapshotStateTransitionService::class);
    $snapshotStateTransitionService->transitionToSnapshotState($snapshot, $established);

    // Skipping approval must not send the sign request that the approved step
    // would otherwise trigger.
    Notification::assertNotSentTo($approver, SnapshotApprovalSignRequest::class);
});

it('does notify approvers on a normal transition to approved', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();
    $this->be($user);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => InReview::class,
        ]);
    $approver = User::factory()
        ->hasAttached($organisation)
        ->create();
    SnapshotApproval::factory()
        ->for($snapshot)
        ->for($approver, 'assignedTo')
        ->create();

    /** @var SnapshotState $approved */
    $approved = SnapshotState::make(Approved::class, $snapshot);

    // Fake only from here so the approval-created observer notification is not
    // counted: we assert purely about the transition's own notification.
    Notification::fake();

    $snapshotStateTransitionService = $this->app->get(SnapshotStateTransitionService::class);
    $snapshotStateTransitionService->transitionToSnapshotState($snapshot, $approved);

    Notification::assertSentTo($approver, SnapshotApprovalSignRequest::class);
});
