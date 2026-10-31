<?php

declare(strict_types=1);

namespace App\Services\AuditLog\Authentication;

use App\Models\User;
use App\Services\AuditLog\AuditEvent;

class LogoutEvent implements AuditEvent
{
    public function __construct(
        public readonly User $user,
    ) {
    }
}
