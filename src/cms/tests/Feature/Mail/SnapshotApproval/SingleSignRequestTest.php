<?php

declare(strict_types=1);

use App\Mail\SnapshotApproval\SingleSignRequest;
use App\Models\SnapshotApproval;
use App\Models\SnapshotApprovalLog;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('has the correct content', function (): void {
    $snapshotApproval = SnapshotApproval::factory()->create();

    $mailable = new SingleSignRequest($snapshotApproval);
    $mailable->assertHasSubject(
        __('snapshot_approval.mail_single_sign_request_subject'),
    );
    $mailable->assertSeeInHtml(__('snapshot_approval.mail_single_sign_request_text'));
});

it('logs the snapshotApproval', function (): void {
    $snapshotApproval = SnapshotApproval::factory()
        ->create([
            'notified_at' => null,
            'requested_by' => User::factory(),
        ]);

    Mail::to($snapshotApproval->assignedTo)
        ->send(new SingleSignRequest($snapshotApproval));

    $snapshotApproval->refresh();

    expect($snapshotApproval->notified_at)
        ->not()->toBeNull();

    $this->assertDatabaseHas(SnapshotApprovalLog::class, [
        'snapshot_id' => $snapshotApproval->snapshot->id->toString(),
        'user_id' => $snapshotApproval->requestedBy->id->toString(),
    ]);
});

it('logs the snapshotApproval when requestedBy is null', function (): void {
    $snapshotApproval = SnapshotApproval::factory()
        ->create([
            'notified_at' => null,
            'requested_by' => null,
        ]);

    Mail::to($snapshotApproval->assignedTo)
        ->send(new SingleSignRequest($snapshotApproval));

    $snapshotApproval->refresh();

    expect($snapshotApproval->notified_at)
        ->not()->toBeNull();

    $this->assertDatabaseHas(SnapshotApprovalLog::class, [
        'snapshot_id' => $snapshotApproval->snapshot->id->toString(),
        'user_id' => $snapshotApproval->assignedTo->id->toString(),
    ]);
});
