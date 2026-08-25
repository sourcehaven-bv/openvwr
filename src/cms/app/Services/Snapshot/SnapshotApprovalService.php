<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use App\Enums\Notification\NotificationStream;
use App\Enums\Snapshot\SnapshotApprovalLogMessageType;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Mail\SnapshotApproval\ApprovalNotification;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\SnapshotApprovalLog;
use App\Models\User;
use App\Services\Notification\NotificationRecipientService;
use Illuminate\Support\Facades\Mail;

class SnapshotApprovalService
{
    public function __construct(
        private readonly NotificationRecipientService $notificationRecipientService,
    ) {
    }

    public function create(Snapshot $snapshot, User $requestedBy, User $assignedTo): void
    {
        $snapshotApproval = new SnapshotApproval();
        $snapshotApproval->snapshot_id = $snapshot->id;
        $snapshotApproval->requested_by = $requestedBy->id;
        $snapshotApproval->assigned_to = $assignedTo->id;
        $snapshotApproval->save();

        SnapshotApprovalLog::create([
            'snapshot_id' => $snapshot->id,
            'user_id' => $requestedBy->id,
            'message' => [
                'type' => SnapshotApprovalLogMessageType::APPROVAL_REQUEST,
                'assigned_to' => $snapshotApproval->assignedTo->logName,
            ],
        ]);
    }

    public function delete(SnapshotApproval $snapshotApproval, User $user): void
    {
        SnapshotApprovalLog::create([
            'snapshot_id' => $snapshotApproval->snapshot->id,
            'user_id' => $user->id,
            'message' => [
                'type' => SnapshotApprovalLogMessageType::APPROVAL_REQUEST_DELETE,
                'assigned_to' => $snapshotApproval->assignedTo->logName,
            ],
        ]);

        $snapshotApproval->delete();
    }

    public function setStatus(User $user, SnapshotApproval $snapshotApproval, SnapshotApprovalStatus $status, ?string $notes = null): void
    {
        $snapshotApproval->status = $status;
        $snapshotApproval->save();

        SnapshotApprovalLog::create([
            'snapshot_id' => $snapshotApproval->snapshot->id,
            'user_id' => $user->id,
            'message' => [
                'type' => SnapshotApprovalLogMessageType::APPROVAL_UPDATE,
                'assigned_to' => $snapshotApproval->assignedTo->logName,
                'status' => $status->value,
                'notes' => $notes,
            ],
        ]);

        $recipients = $this->notificationRecipientService->getRecipients(
            NotificationStream::SNAPSHOT_APPROVAL_UPDATED,
            $snapshotApproval->snapshot->organisation,
        );

        foreach ($recipients as $recipient) {
            Mail::to($recipient)
                ->queue(new ApprovalNotification($snapshotApproval));
        }
    }
}
