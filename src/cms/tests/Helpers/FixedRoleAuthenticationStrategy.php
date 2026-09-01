<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\Principal;
use App\Models\User;
use App\Services\Authentication\AuthenticationStrategy;
use RuntimeException;

/**
 * A strategy that only answers the question the manual asks of it: which roles
 * are in effect. The user and organisation are never read on that path.
 */
class FixedRoleAuthenticationStrategy implements AuthenticationStrategy
{
    /**
     * @param array<Role> $roles
     */
    public function __construct(private readonly array $roles)
    {
    }

    public function user(): User
    {
        throw new RuntimeException('The manual does not read the user.');
    }

    public function organisation(): Organisation
    {
        throw new RuntimeException('The manual does not read the organisation.');
    }

    public function principal(): Principal
    {
        return new Principal($this->roles);
    }
}
