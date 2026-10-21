<?php

declare(strict_types=1);

namespace App\Listeners\Authentication;

use App\Models\User;
use App\Services\AuditLog\AuditLogger;
use App\Services\AuditLog\Authentication\LogoutEvent;
use Illuminate\Auth\Events\Logout;

class LogoutHandler
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function handle(Logout $event): void
    {
        // The guard also fires Logout when nobody was logged in.
        if (!$event->user instanceof User) {
            return;
        }

        $this->auditLogger->register(new LogoutEvent($event->user));
    }
}
