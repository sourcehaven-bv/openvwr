<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Models\Organisation;
use App\Models\Principal;
use App\Models\User;
use App\Services\Authentication\AuthenticationStrategy;
use RuntimeException;

/**
 * A strategy that owns the front door elsewhere, the way the proxy does: this
 * application has no login page of its own under it.
 *
 * The real Pratique strategy would drag its verifier, JWKS fetch and an assertion
 * into a test that is only about the "nowhere to redirect to" branch.
 */
class NoLoginPageAuthenticationStrategy implements AuthenticationStrategy
{
    public function user(): User
    {
        throw new RuntimeException('This test never reaches the user.');
    }

    public function organisation(): Organisation
    {
        throw new RuntimeException('This test never reaches the organisation.');
    }

    public function principal(): Principal
    {
        throw new RuntimeException('This test never reaches the principal.');
    }

    /** @return array<int, class-string> */
    public function panelMiddleware(): array
    {
        return [];
    }

    public function loginPage(): ?string
    {
        return null;
    }

    public function hasLoginPage(): bool
    {
        return false;
    }
}
