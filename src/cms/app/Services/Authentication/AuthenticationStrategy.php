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

    /**
     * The middleware that gates a panel request under this strategy.
     *
     * Owned here rather than by the panel because "how is a request gated" is the
     * same decision as "how is identity established" — a strategy that reads a
     * session needs the session guard, one that reads a signed assertion needs
     * the verifier instead. Leaving the two in separate places lets them drift:
     * a panel that still checks for a session a strategy never creates.
     *
     * @return array<int, class-string>
     */
    public function panelMiddleware(): array;

    /**
     * The panel's login page, or null when this strategy has none.
     *
     * Null is not "use the default" — it means this application owns no login
     * page at all, because something in front of it does.
     *
     * @return class-string|null
     */
    public function loginPage(): ?string;

    /**
     * Whether this application can send an unauthenticated visitor somewhere to
     * sign in.
     *
     * False means something in front of the application owns the front door, so
     * an unauthenticated request must be REFUSED rather than redirected. Asked
     * as its own question because callers act on the answer, not on the page.
     */
    public function hasLoginPage(): bool;
}
