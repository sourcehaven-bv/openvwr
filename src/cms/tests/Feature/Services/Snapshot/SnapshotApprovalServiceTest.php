<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Enums\Notification\NotificationStream;
use App\Enums\Snapshot\SnapshotApprovalLogMessageType;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Mail\SnapshotApproval\ApprovalNotification;
use App\Models\Organisation;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\SnapshotApprovalLog;
use App\Models\User;
use App\Services\Snapshot\SnapshotApprovalService;
use Illuminate\Support\Facades\Mail;

it('can create', function (): void {
    $snapshot = Snapshot::factory()->create();
    $user = User::factory()->create();

    /** @var SnapshotApprovalService $snapshotApprovalService */
    $snapshotApprovalService = $this->app->get(SnapshotApprovalService::class);
    $snapshotApprovalService->create($snapshot, $user, $user);

    $this->assertDatabaseHas(SnapshotApproval::class, [
        'snapshot_id' => $snapshot->id,
    ]);
    $this->assertDatabaseHas(SnapshotApprovalLog::class, [
        'snapshot_id' => $snapshot->id,
    ]);
});

it('can delete', function (): void {
    $snapshot = Snapshot::factory()->create();
    $snapshotApproval = SnapshotApproval::factory()
        ->for($snapshot)
        ->create();
    $user = User::factory()->create();

    /** @var SnapshotApprovalService $snapshotApprovalService */
    $snapshotApprovalService = $this->app->get(SnapshotApprovalService::class);
    $snapshotApprovalService->delete($snapshotApproval, $user);

    $this->assertDatabaseMissing(SnapshotApproval::class, [
        'id' => $snapshotApproval->id,
    ]);
    $this->assertDatabaseHas(SnapshotApprovalLog::class, [
        'snapshot_id' => $snapshot->id,
    ]);
});

it('can set status to approved', function (): void {
    $snapshot = Snapshot::factory()->create();
    $snapshotApproval = SnapshotApproval::factory()
        ->for($snapshot)
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);
    $user = User::factory()->create();
    Mail::fake();

    /** @var SnapshotApprovalService $snapshotApprovalService */
    $snapshotApprovalService = $this->app->get(SnapshotApprovalService::class);
    $snapshotApprovalService->setStatus($user, $snapshotApproval, SnapshotApprovalStatus::APPROVED);

    $snapshotApproval->refresh();
    expect($snapshotApproval->status)
        ->toBe(SnapshotApprovalStatus::APPROVED);
    $this->assertDatabaseHas(SnapshotApprovalLog::class, [
        'snapshot_id' => $snapshot->id,
        'user_id' => $user->id,
        'message->type' => SnapshotApprovalLogMessageType::APPROVAL_UPDATE,
    ]);

    Mail::assertNotSent(ApprovalNotification::class);
});

it('can set status to approved with notification to other approvals', function (): void {
    $organisation = Organisation::factory()->create();

    $snapshot = Snapshot::factory()
        ->for($organisation)
        ->create();
    $snapshotApproval = SnapshotApproval::factory()
        ->for($snapshot)
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);
    $user = User::factory()
        ->hasAttached($organisation)
        ->create();
    User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();
    Mail::fake();

    /** @var SnapshotApprovalService $snapshotApprovalService */
    $snapshotApprovalService = $this->app->get(SnapshotApprovalService::class);
    $snapshotApprovalService->setStatus($user, $snapshotApproval, SnapshotApprovalStatus::APPROVED);

    $snapshotApproval->refresh();
    expect($snapshotApproval->status)
        ->toBe(SnapshotApprovalStatus::APPROVED);

    Mail::assertQueued(ApprovalNotification::class);
});

it('will not notify a privacy officer that excluded the stream', function (): void {
    $organisation = Organisation::factory()->create();

    $snapshot = Snapshot::factory()
        ->for($organisation)
        ->create();
    $snapshotApproval = SnapshotApproval::factory()
        ->for($snapshot)
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);
    $user = User::factory()
        ->hasAttached($organisation)
        ->create();
    User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create([
            'notification_exclusions' => [NotificationStream::SNAPSHOT_APPROVAL_UPDATED],
        ]);
    Mail::fake();

    /** @var SnapshotApprovalService $snapshotApprovalService */
    $snapshotApprovalService = $this->app->get(SnapshotApprovalService::class);
    $snapshotApprovalService->setStatus($user, $snapshotApproval, SnapshotApprovalStatus::APPROVED);

    Mail::assertNotQueued(ApprovalNotification::class);
});

it('can set status to declined', function (): void {
    $snapshot = Snapshot::factory()->create();
    $snapshotApproval = SnapshotApproval::factory()
        ->for($snapshot)
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);
    $user = User::factory()->create();

    /** @var SnapshotApprovalService $snapshotApprovalService */
    $snapshotApprovalService = $this->app->get(SnapshotApprovalService::class);
    $snapshotApprovalService->setStatus($user, $snapshotApproval, SnapshotApprovalStatus::DECLINED, fake()->sentence());

    $snapshotApproval->refresh();
    expect($snapshotApproval->status)
        ->toBe(SnapshotApprovalStatus::DECLINED);
});
