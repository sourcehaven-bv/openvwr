<?php

declare(strict_types=1);

namespace App\Services\AuditLog\Authentication;

use App\Models\User;
use App\Services\AuditLog\AuditEvent;

class TwoFactorFailedEvent implements AuditEvent
{
    public const INVALID_CODE = 'InvalidCode';
    public const TOO_MANY_ATTEMPTS = 'TooManyAttempts';

    private function __construct(
        public readonly User $user,
        public readonly string $reason,
    ) {
    }

    public static function invalidCode(User $user): self
    {
        return new self($user, self::INVALID_CODE);
    }

    public static function tooManyAttempts(User $user): self
    {
        return new self($user, self::TOO_MANY_ATTEMPTS);
    }
}
