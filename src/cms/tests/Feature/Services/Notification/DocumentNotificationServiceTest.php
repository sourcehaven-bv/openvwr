<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Enums\Notification\NotificationStream;
use App\Mail\Document\DocumentNotification;
use App\Models\Document;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Notification\DocumentNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;

it('will notify the chief privacy officer', function (): void {
    Mail::fake();

    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();

    $document = Document::factory()
        ->recycle($organisation)
        ->create([
            'notify_at' => CarbonImmutable::today(),
        ]);

    /** @var DocumentNotificationService $documentNotificationService */
    $documentNotificationService = $this->app->get(DocumentNotificationService::class);
    $documentNotificationService->notifyAllWithDate(CarbonImmutable::today());

    Mail::assertQueued(
        DocumentNotification::class,
        static function (DocumentNotification $mail) use ($document, $user): bool {
            if ($mail->to[0]['address'] !== $user->email) {
                return false;
            }

            $logContext = $mail->getLogContext();
            return $logContext['document_id'] === $document->id->toString();
        },
    );
});

it('will not notify a privacy officer that excluded the stream', function (): void {
    Mail::fake();

    $organisation = Organisation::factory()->create();
    User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create([
            'notification_exclusions' => [NotificationStream::DOCUMENT_NOTIFY_DATE_REACHED],
        ]);

    Document::factory()
        ->recycle($organisation)
        ->create([
            'notify_at' => CarbonImmutable::today(),
        ]);

    /** @var DocumentNotificationService $documentNotificationService */
    $documentNotificationService = $this->app->get(DocumentNotificationService::class);
    $documentNotificationService->notifyAllWithDate(CarbonImmutable::today());

    Mail::assertNotQueued(DocumentNotification::class);
});
