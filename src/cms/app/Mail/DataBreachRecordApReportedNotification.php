<?php

declare(strict_types=1);

namespace App\Mail;

use App\Filament\Resources\DataBreachRecord\Pages\EditDataBreachRecord;
use App\Models\DataBreachRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

use function __;

class DataBreachRecordApReportedNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $link;

    public function __construct(
        public DataBreachRecord $dataBreachRecord,
    ) {
        $this->link = EditDataBreachRecord::getUrl([
            'record' => $dataBreachRecord,
            'tenant' => $dataBreachRecord->organisation,
        ]);
    }

    public function getLogContext(): array
    {
        return [
            'data_breach_record_id' => $this->dataBreachRecord->id->toString(),
        ];
    }

    public function getSubject(): string
    {
        return __('data_breach_record.mail_notification_subject');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.data_breach_record.ap_reported_notification');
    }
}
