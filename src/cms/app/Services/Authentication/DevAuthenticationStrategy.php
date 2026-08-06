<?php

declare(strict_types=1);

namespace App\Services\Authentication;

/**
 * Local-development identity: pick a user from a dropdown, no credentials.
 *
 * Once a session exists it resolves exactly like the builtin strategy — it
 * differs only in how that session is *established* (see the DevLogin page).
 * That is deliberate: it exercises the strategy seam without duplicating the
 * resolution logic under test, so a leak in the seam shows up here rather than
 * being masked by a parallel implementation.
 *
 * This is an authentication bypass. It must never be reachable in production;
 * AuthenticationStrategyFactory refuses to build it outside local/testing, and
 * the Filament page is only registered for the same environments.
 */
class DevAuthenticationStrategy extends BuiltinAuthenticationStrategy
{
}
