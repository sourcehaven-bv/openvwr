<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\Notification\NotificationStream;
use App\Mail\DataBreachRecordApReportedNotification;
use App\Models\DataBreachRecord;
use Illuminate\Support\Facades\Mail;

class DataBreachNotificationService
{
    public function __construct(
        private readonly NotificationRecipientService $notificationRecipientService,
    ) {
    }

    public function sendNotifications(DataBreachRecord $dataBreachRecord): void
    {
        $recipients = $this->notificationRecipientService->getRecipients(
            NotificationStream::DATA_BREACH_AP_REPORTED,
            $dataBreachRecord->organisation,
        );

        foreach ($recipients as $recipient) {
            Mail::to($recipient)
                ->queue(new DataBreachRecordApReportedNotification($dataBreachRecord));
        }
    }
}
