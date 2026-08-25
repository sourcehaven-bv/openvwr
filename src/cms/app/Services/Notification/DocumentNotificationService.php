<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\Notification\NotificationStream;
use App\Mail\Document\DocumentNotification;
use App\Models\Document;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;

readonly class DocumentNotificationService
{
    public function __construct(
        private NotificationRecipientService $notificationRecipientService,
    ) {
    }

    public function notifyAllWithDate(CarbonImmutable $carbonImmutable): void
    {
        $documents = Document::where('notify_at', $carbonImmutable)->get();

        foreach ($documents as $document) {
            $this->notify($document);
        }
    }

    private function notify(Document $document): void
    {
        $recipients = $this->notificationRecipientService->getRecipients(
            NotificationStream::DOCUMENT_NOTIFY_DATE_REACHED,
            $document->organisation,
        );

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)
                ->queue(new DocumentNotification($document));
        }
    }
}
