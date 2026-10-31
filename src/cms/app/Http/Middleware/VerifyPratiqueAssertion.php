<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Authentication\Pratique\PratiqueAssertionException;
use App\Services\Authentication\Pratique\PratiqueAssertionVerifier;
use App\Services\Authentication\Pratique\PratiqueContext;
use App\Services\Authentication\Pratique\PratiqueIdentityResolver;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

use function abort;
use function is_string;

/**
 * Authenticates a request from the proxy's signed assertion.
 *
 * Fails **closed**, and deliberately never redirects to a login page: under this
 * driver the app owns no login route, and a redirect would turn a verification
 * failure into a loop through the proxy. A request without a verifiable assertion
 * is a 403 — including one that reached the app directly, bypassing the proxy.
 *
 * The reason is logged for operators but never returned: telling a caller which
 * check failed hands them an oracle for probing the boundary.
 */
class VerifyPratiqueAssertion
{
    public function __construct(
        private readonly PratiqueAssertionVerifier $verifier,
        private readonly PratiqueIdentityResolver $resolver,
        private readonly PratiqueContext $context,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $assertion = $this->verifier->verify($request);

            // Filament resolves the tenant from the URL while the proxy resolves it
            // from the assertion. If those disagree, the user is asking for an
            // organisation this assertion does not grant — which must be a hard
            // failure, not a silent switch to whichever one wins.
            $this->assertTenantMatches($request, $assertion->organisationSlug);

            $identity = $this->resolver->resolve($assertion);
            $this->context->set($identity);

            // Filament's IdentifyTenant resolves the URL tenant against the panel
            // guard's user, and 404s when there is none. Setting the user for this
            // request only (no session write) lets that check, and anything else
            // that asks the guard, see the identity the assertion established.
            Filament::auth()->setUser($identity->user);
        } catch (PratiqueAssertionException $exception) {
            Log::warning('Pratique assertion rejected', [
                'reason' => $exception->getMessage(),
                'path' => $request->path(),
            ]);

            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

    /**
     * @throws PratiqueAssertionException
     */
    private function assertTenantMatches(Request $request, string $assertedSlug): void
    {
        $route = $request->route();

        if ($route === null) {
            return;
        }

        $tenant = $route->parameter('tenant');

        // Routes without a tenant segment (the landing redirect, health checks) are
        // legitimately un-scoped; there is nothing to compare.
        if (!is_string($tenant)) {
            return;
        }

        if ($tenant !== $assertedSlug) {
            throw PratiqueAssertionException::tenantMismatch($tenant, $assertedSlug);
        }
    }
}
