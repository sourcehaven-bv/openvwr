<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Models\Organisation;
use App\Models\Principal;
use App\Models\User;
use App\Services\Authentication\AuthenticationStrategy;
use RuntimeException;

/**
 * A strategy whose gate is one middleware that records having run, so the test
 * can tell the pipeline branch apart from the pass-through branch.
 */
class RecordingAuthenticationStrategy implements AuthenticationStrategy
{
    /** Process-scoped, so every test that uses it must reset it. */
    public static bool $ran = false;

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
        return [RecordingMiddleware::class];
    }

    public function loginPage(): ?string
    {
        throw new RuntimeException('This test does not build the panel.');
    }

    public function hasLoginPage(): bool
    {
        throw new RuntimeException('This test does not build the panel.');
    }
}
