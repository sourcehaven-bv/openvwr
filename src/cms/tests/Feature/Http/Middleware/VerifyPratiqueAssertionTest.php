<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyPratiqueAssertion;
use App\Models\Organisation;
use App\Services\Authentication\Pratique\PratiqueContext;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Helpers\PratiqueTestHelper;

/*
 * The middleware is where a request either becomes authenticated or is refused.
 * It must fail CLOSED: no assertion means 403, never a redirect to a login page
 * (this driver has none) and never a pass-through.
 */

function pratiqueMiddleware(): VerifyPratiqueAssertion
{
    return app(VerifyPratiqueAssertion::class);
}

/** A request bound to a route carrying a {tenant} segment, as the panel's are. */
function tenantRequest(?string $token, string $tenant = 'acme'): Request
{
    $request = Request::create('/' . $tenant . '/dashboard', 'GET');

    if ($token !== null) {
        $request->headers->set('Authorization', 'Bearer ' . $token);
    }

    $route = new Route(['GET'], '/{tenant}/dashboard', static fn (): string => 'ok');
    $route->bind($request);
    $route->setParameter('tenant', $tenant);
    $request->setRouteResolver(static fn (): Route => $route);

    return $request;
}

beforeEach(function (): void {
    $this->pratique = new PratiqueTestHelper();
    $this->pratique->publishJwks();
    Log::spy();
});

it('lets a valid assertion through and records the identity', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    $request = tenantRequest($this->pratique->assertion([
        'sub' => 'usr_alice',
        'email' => 'alice@example.org',
        'org_slug' => 'acme',
    ]));

    $response = pratiqueMiddleware()->handle($request, static fn (): string => 'passed');

    expect($response)->toBe('passed')
        ->and(app(PratiqueContext::class)->get()->user->email)->toBe('alice@example.org');
});

/*
 * A request reaching the app without an assertion is either a misconfiguration or
 * someone talking to the app directly, around the proxy. Both must be refused.
 */
it('refuses a request that carries no assertion', function (): void {
    pratiqueMiddleware()->handle(tenantRequest(null), static fn (): string => 'passed');
})->throws(HttpException::class);

it('refuses an assertion for a different upstream', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    pratiqueMiddleware()->handle(
        tenantRequest($this->pratique->assertion(['aud' => 'app://elsewhere'])),
        static fn (): string => 'passed',
    );
})->throws(HttpException::class);

/*
 * Filament resolves the tenant from the URL; the proxy resolves it from the
 * assertion. If they disagree the user is asking for an organisation this
 * assertion does not grant — a hard failure, not a silent switch to whichever
 * source happens to win.
 */
it('refuses a URL tenant that the assertion does not grant', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);
    Organisation::factory()->create(['slug' => 'other-org']);

    pratiqueMiddleware()->handle(
        tenantRequest($this->pratique->assertion(['org_slug' => 'acme']), 'other-org'),
        static fn (): string => 'passed',
    );
})->throws(HttpException::class);

it('answers 403 rather than redirecting to a login page', function (): void {
    try {
        pratiqueMiddleware()->handle(tenantRequest(null), static fn (): string => 'passed');
    } catch (HttpException $exception) {
        expect($exception->getStatusCode())->toBe(403);

        return;
    }

    $this->fail('An unauthenticated request should have been refused.');
});

/*
 * The rejection reason goes to the log for operators, never to the caller:
 * telling someone which check they failed hands them an oracle for probing the
 * boundary.
 */
it('logs why an assertion was rejected', function (): void {
    try {
        pratiqueMiddleware()->handle(tenantRequest(null), static fn (): string => 'passed');
    } catch (HttpException) {
        // expected
    }

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message): bool => $message === 'Pratique assertion rejected');
});

/* Un-scoped routes (the landing redirect, health checks) have no tenant to compare. */
it('allows a route with no tenant segment', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    $request = Request::create('/', 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $this->pratique->assertion(['org_slug' => 'acme']));

    expect(pratiqueMiddleware()->handle($request, static fn (): string => 'passed'))->toBe('passed');
});

/*
 * A request that never went through routing (an early-terminating middleware, a
 * direct dispatch) has no tenant parameter to compare against.
 */
it('allows a request with no resolved route', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    $request = Request::create('/acme/dashboard', 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $this->pratique->assertion(['org_slug' => 'acme']));
    $request->setRouteResolver(static fn (): ?Route => null);

    expect(pratiqueMiddleware()->handle($request, static fn (): string => 'passed'))->toBe('passed');
});

/*
 * An empty tenant segment is not the organisation the assertion grants, so it is
 * refused like any other mismatch rather than waved through.
 */
it('refuses a route whose tenant parameter is empty', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    $request = Request::create('/dashboard', 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $this->pratique->assertion(['org_slug' => 'acme']));

    $route = new Route(['GET'], '/{tenant}/dashboard', static fn (): string => 'ok');
    $route->bind($request);
    $route->setParameter('tenant', '');
    $request->setRouteResolver(static fn (): Route => $route);

    pratiqueMiddleware()->handle($request, static fn (): string => 'passed');
})->throws(HttpException::class);

/*
 * A resolved route that simply has no {tenant} segment — the landing redirect,
 * the health checks — is legitimately un-scoped, so there is nothing to compare
 * the assertion against and the request proceeds.
 */
it('allows a resolved route that has no tenant segment', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    $request = Request::create('/health', 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $this->pratique->assertion(['org_slug' => 'acme']));

    $route = new Route(['GET'], '/health', static fn (): string => 'ok');
    $route->bind($request);
    $request->setRouteResolver(static fn (): Route => $route);

    expect(pratiqueMiddleware()->handle($request, static fn (): string => 'passed'))->toBe('passed');
});
