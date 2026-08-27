<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Enums\Notification\NotificationStream;
use App\Mail\DataBreachRecordApReportedNotification;
use App\Models\DataBreachRecord;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Notification\DataBreachNotificationService;
use Illuminate\Support\Facades\Mail;

it('will notify the chief privacy officer', function (): void {
    Mail::fake();

    $organisation = Organisation::factory()->create();
    $chiefPrivacyOfficer = User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::CHIEF_PRIVACY_OFFICER, $organisation)
        ->create();

    $dataProtectionOfficial = User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::DATA_PROTECTION_OFFICIAL, $organisation)
        ->create();

    $dataBreachRecord = DataBreachRecord::factory()
        ->for($organisation)
        ->create();

    /** @var DataBreachNotificationService $dataBreachNotificationService */
    $dataBreachNotificationService = $this->app->get(DataBreachNotificationService::class);
    $dataBreachNotificationService->sendNotifications($dataBreachRecord);

    Mail::assertQueued(
        DataBreachRecordApReportedNotification::class,
        static function (DataBreachRecordApReportedNotification $mail) use ($dataBreachRecord, $chiefPrivacyOfficer): bool {
            if ($mail->to[0]['address'] !== $chiefPrivacyOfficer->email) {
                return false;
            }

            $logContext = $mail->getLogContext();
            return $logContext['data_breach_record_id'] === $dataBreachRecord->id->toString();
        },
    );
    Mail::assertQueued(
        DataBreachRecordApReportedNotification::class,
        static function (DataBreachRecordApReportedNotification $mail) use ($dataBreachRecord, $dataProtectionOfficial): bool {
            if ($mail->to[0]['address'] !== $dataProtectionOfficial->email) {
                return false;
            }

            $logContext = $mail->getLogContext();
            return $logContext['data_breach_record_id'] === $dataBreachRecord->id->toString();
        },
    );
});

it('will not notify a recipient that excluded the stream', function (): void {
    Mail::fake();

    $organisation = Organisation::factory()->create();
    User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::CHIEF_PRIVACY_OFFICER, $organisation)
        ->create([
            'notification_exclusions' => [NotificationStream::DATA_BREACH_AP_REPORTED],
        ]);

    $dataProtectionOfficial = User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::DATA_PROTECTION_OFFICIAL, $organisation)
        ->create();

    $dataBreachRecord = DataBreachRecord::factory()
        ->for($organisation)
        ->create();

    /** @var DataBreachNotificationService $dataBreachNotificationService */
    $dataBreachNotificationService = $this->app->get(DataBreachNotificationService::class);
    $dataBreachNotificationService->sendNotifications($dataBreachRecord);

    // Scoped to this mailable: creating users queues UserCreatedMailable too.
    Mail::assertQueued(
        DataBreachRecordApReportedNotification::class,
        static fn (DataBreachRecordApReportedNotification $mail): bool
            => $mail->to[0]['address'] === $dataProtectionOfficial->email,
    );
    Mail::assertQueued(DataBreachRecordApReportedNotification::class, 1);
});
