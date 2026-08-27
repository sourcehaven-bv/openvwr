<?php

declare(strict_types=1);

namespace App\Enums\Notification;

use App\Enums\Authorization\Role;

use function in_array;

/**
 * A stream of role-based emails a user can opt out of.
 *
 * Every stream is on by default: a user only stops receiving one by adding it
 * to their exclusion list (see User::notification_exclusions). A new stream is
 * therefore active for everyone the moment it is added here, which is the safe
 * default for a register where missing a notification matters more than an
 * extra mail.
 *
 * Every stream is also opt-out-able, including the data breach one. The
 * recipients are professionals: if they do not want a mail, we respect that
 * rather than locking the checkbox. Do not add a "mandatory" stream here.
 */
enum NotificationStream: string
{
    case DATA_BREACH_AP_REPORTED = 'data-breach-ap-reported';
    case DOCUMENT_NOTIFY_DATE_REACHED = 'document-notify-date-reached';
    case SNAPSHOT_APPROVAL_UPDATED = 'snapshot-approval-updated';
    case SNAPSHOT_CREATED = 'snapshot-created';

    /**
     * The roles that receive this stream. Also determines who is offered the
     * setting: opting out of a stream you never receive is a no-op, so it is
     * not shown.
     *
     * @return array<Role>
     */
    public function roles(): array
    {
        return match ($this) {
            self::DATA_BREACH_AP_REPORTED => [Role::CHIEF_PRIVACY_OFFICER, Role::DATA_PROTECTION_OFFICIAL],
            self::DOCUMENT_NOTIFY_DATE_REACHED => [Role::PRIVACY_OFFICER],
            self::SNAPSHOT_APPROVAL_UPDATED => [Role::PRIVACY_OFFICER],
            self::SNAPSHOT_CREATED => [Role::CHIEF_PRIVACY_OFFICER, Role::PRIVACY_OFFICER],
        };
    }

    /**
     * Streams a user with any of the given roles can be offered in their profile.
     *
     * @param array<Role> $roles
     *
     * @return array<NotificationStream>
     */
    public static function casesForRoles(array $roles): array
    {
        $streams = [];

        foreach (self::cases() as $notificationStream) {
            foreach ($notificationStream->roles() as $role) {
                if (!in_array($role, $roles, true)) {
                    continue;
                }

                $streams[] = $notificationStream;

                break;
            }
        }

        return $streams;
    }
}
