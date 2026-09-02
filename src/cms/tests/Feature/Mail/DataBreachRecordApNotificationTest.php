<?php

declare(strict_types=1);

use App\Filament\Resources\DataBreachRecord\Pages\EditDataBreachRecord;
use App\Mail\DataBreachRecordApReportedNotification;
use App\Models\DataBreachRecord;

it('has the correct content', function (): void {
    $dataBreachRecord = DataBreachRecord::factory()->create();
    $mailable = new DataBreachRecordApReportedNotification($dataBreachRecord);

    $link = EditDataBreachRecord::getUrl([
        'record' => $dataBreachRecord,
        'tenant' => $dataBreachRecord->organisation,
    ]);

    $mailable->assertHasSubject(__('data_breach_record.mail_notification_subject'));
    $mailable->assertSeeInHtml(__('data_breach_record.mail_notification_text'));
    $mailable->assertSeeInHtml($link);
});
