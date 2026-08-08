<?php

declare(strict_types=1);

namespace App\Services\Authentication;

use App\Models\Organisation;
use App\Models\Principal;
use App\Models\User;

/**
 * How the application answers "who is acting, and in which organisation".
 *
 * This is the seam between the app and whatever establishes identity. Everything
 * above it — the ~50 callers of the Authentication facade, AuthorizationService,
 * and all policies — is written against these three questions and never learns
 * which strategy answered them.
 *
 * Implementations are selected by the `auth.driver` config value.
 */
interface AuthenticationStrategy
{
    /**
     * The authenticated user. Implementations throw when there is none — callers
     * treat "no user" as a programming error, not a branch, because every path
     * reaching them is already behind auth middleware.
     */
    public function user(): User;

    /** The organisation the current request acts in (the active tenant). */
    public function organisation(): Organisation;

    /**
     * The roles in effect for this request: global roles plus the roles held in
     * the current organisation.
     */
    public function principal(): Principal;
}
