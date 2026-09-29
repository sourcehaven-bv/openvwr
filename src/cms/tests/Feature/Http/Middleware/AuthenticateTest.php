<?php

declare(strict_types=1);

use App\Http\Middleware\Authenticate;
use App\Services\Authentication\AuthenticationStrategy;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Helpers\NoLoginPageAuthenticationStrategy;

/*
 * Where an unauthenticated request goes is the strategy's call. Under a driver
 * that owns the front door itself there is no login route in this app at all, so
 * the redirect target cannot be built — and building it anyway turns "not signed
 * in" into a 500 instead of a refusal.
 */

afterEach(function (): void {
    // The binding outlives the test, so put it back.
    app()->forgetInstance(AuthenticationStrategy::class);
});

/** Reaches the protected redirectTo() the way the framework does. */
function redirectTarget(Request $request): ?string
{
    $middleware = new Authenticate(app('auth'));

    return (fn (): ?string => $this->redirectTo($request))->call($middleware);
}

it('refuses instead of redirecting when the strategy has no login page', function (): void {
    app()->forgetInstance(AuthenticationStrategy::class);
    app()->instance(AuthenticationStrategy::class, new NoLoginPageAuthenticationStrategy());

    expect(static fn (): ?string => redirectTarget(Request::create('/')))
        ->toThrow(fn (HttpException $e) => expect($e->getStatusCode())->toBe(403));
});
