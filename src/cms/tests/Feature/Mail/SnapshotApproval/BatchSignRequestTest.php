<?php

declare(strict_types=1);

use App\Mail\SnapshotApproval\BatchSignRequest;
use App\Models\Organisation;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\SnapshotApprovalLog;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('has the correct content', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasAttached($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->for($organisation)
        ->create();
    $snapshotApprovalsNew = SnapshotApproval::factory()
        ->for($snapshot)
        ->count(3)
        ->createQuietly([
            'notified_at' => null,
        ]);
    $snapshotApprovalsExisting = SnapshotApproval::factory()
        ->for($snapshot)
        ->count(3)
        ->createQuietly([
            'notified_at' => fake()->dateTime(),
        ]);

    $mailable = new BatchSignRequest($user, $organisation, $snapshotApprovalsNew, $snapshotApprovalsExisting);
    $mailable->assertHasSubject(
        __('snapshot_approval.mail_batch_sign_request_subject', ['organisationName' => $organisation->name]),
    );
    $mailable->assertSeeInHtml(__('snapshot_approval.mail_batch_sign_request_text'));
});

it('does not log the snapshotApproval', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasAttached($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->for($organisation)
        ->create();
    $snapshotApprovalsNew = SnapshotApproval::factory()
        ->for($snapshot)
        ->count(3)
        ->createQuietly([
            'notified_at' => null,
        ]);
    $snapshotApprovalsExisting = SnapshotApproval::factory()
        ->for($snapshot)
        ->count(3)
        ->createQuietly([
            'notified_at' => fake()->dateTime(),
        ]);

    Mail::to($user->email)
        ->send(new BatchSignRequest($user, $organisation, $snapshotApprovalsNew, $snapshotApprovalsExisting));

    $this->assertDatabaseEmpty(SnapshotApprovalLog::class);
});
