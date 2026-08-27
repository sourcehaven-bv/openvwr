<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Enums\Notification\NotificationStream;

it('offers only streams that match the given roles', function (): void {
    $streams = NotificationStream::casesForRoles([Role::PRIVACY_OFFICER]);

    expect($streams)->toBe([
        NotificationStream::DOCUMENT_NOTIFY_DATE_REACHED,
        NotificationStream::SNAPSHOT_APPROVAL_UPDATED,
        NotificationStream::SNAPSHOT_CREATED,
    ]);
});

it('offers a stream once when several roles receive it', function (): void {
    $streams = NotificationStream::casesForRoles([
        Role::CHIEF_PRIVACY_OFFICER,
        Role::PRIVACY_OFFICER,
    ]);

    expect($streams)->toContain(NotificationStream::SNAPSHOT_CREATED)
        ->and(array_count_values(array_map(
            static fn (NotificationStream $notificationStream): string => $notificationStream->value,
            $streams,
        )))->each->toBe(1);
});

it('offers nothing to a role without streams', function (): void {
    expect(NotificationStream::casesForRoles([Role::INPUT_PROCESSOR]))->toBe([]);
});

it('has at least one receiving role for every stream', function (): void {
    foreach (NotificationStream::cases() as $notificationStream) {
        expect($notificationStream->roles())->not->toBeEmpty();
    }
});
