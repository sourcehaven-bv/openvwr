<?php

declare(strict_types=1);

use App\Filament\Resources\DocumentResource\Pages\ViewDocument;
use App\Mail\Document\DocumentNotification;
use App\Models\Document;

it('has the correct content', function (): void {
    $document = Document::factory()->create();
    $mailable = new DocumentNotification($document);

    $link = ViewDocument::getUrl([
        'record' => $document,
        'tenant' => $document->organisation,
    ]);

    $mailable->assertHasSubject(__('document.mail_notification_subject'));
    $mailable->assertSeeInHtml(__('document.mail_notification_text'));
    $mailable->assertSeeInHtml($link);
});
