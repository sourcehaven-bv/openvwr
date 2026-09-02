<?php

declare(strict_types=1);

use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Mail\SnapshotApproval\ApprovalNotification;
use App\Models\SnapshotApproval;

it('has the correct content', function (): void {
    $snapshotApproval = SnapshotApproval::factory()->create();
    $mailable = new ApprovalNotification($snapshotApproval);

    $link = ViewSnapshot::getUrl([
        'record' => $snapshotApproval->snapshot,
        'tenant' => $snapshotApproval->snapshot->organisation,
        'tab' => sprintf('-%s-tab', ViewSnapshot::TAB_ID_APPROVAL),
    ]);

    $status = __(sprintf('snapshot_approval_status.%s', $snapshotApproval->status->value));
    $subject = __('snapshot_approval.mail_approval_notification_subject', ['status' => $status]);

    $mailable->assertHasSubject($subject);
    $mailable->assertSeeInHtml(__('snapshot_approval.mail_approval_notification_text'));
    $mailable->assertSeeInHtml($link);
});

it('has the correct log context', function (): void {
    $snapshotApproval = SnapshotApproval::factory()->create();
    $mailable = new ApprovalNotification($snapshotApproval);

    expect($mailable->getLogContext())
        ->toBe([
            'snapshot_id' => $snapshotApproval->snapshot->id->toString(),
        ]);
});
