<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Collections\UserCollection;
use App\Enums\Notification\NotificationStream;
use App\Models\Organisation;
use App\Models\User;
use App\Services\User\UserByRoleService;

readonly class NotificationRecipientService
{
    public function __construct(
        private UserByRoleService $userByRoleService,
    ) {
    }

    /**
     * The users in this organisation that should receive the given stream:
     * everyone holding one of its roles, minus those who opted out.
     */
    public function getRecipients(NotificationStream $notificationStream, Organisation $organisation): UserCollection
    {
        $users = $this->userByRoleService->getUsersByOrganisationRole(
            $organisation,
            $notificationStream->roles(),
        );

        return $users->filter(
            static fn (User $user): bool => $user->receivesNotification($notificationStream),
        );
    }
}
