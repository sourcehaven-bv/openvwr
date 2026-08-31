<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Enums\Authorization\Role;
use App\Services\AuthenticationService;
use App\Services\AuthorizationService;

use function app;

class RoleTestHelper
{
    /**
     * Act as a user holding exactly these roles.
     *
     * AuthorizationService is readonly and therefore cannot be mocked, so this
     * swaps in a real one built on a strategy that reports fixed roles. That
     * keeps the real authorisation logic in the test rather than stubbing it
     * out, while making the roles deterministic.
     *
     * @param array<Role> $roles
     */
    public static function actAs(array $roles): void
    {
        app()->instance(
            AuthorizationService::class,
            new AuthorizationService(
                new AuthenticationService(new FixedRoleAuthenticationStrategy($roles)),
                [],
            ),
        );
    }
}
