<?php

declare(strict_types=1);

use App\Services\Authentication\AuthenticationStrategy;
use App\Services\Authentication\AuthenticationStrategyFactory;
use App\Services\Authentication\PratiqueAuthenticationStrategy;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\Helpers\PratiqueTestHelper;

/*
 * Livewire registers POST /livewire/update itself, on the `web` group alone.
 * That group starts a session and checks CSRF but establishes no identity, so
 * under the pratique driver the endpoint answered every component update
 * unauthenticated: no user, no tenant, and a 404 from Livewire because it could
 * not resolve the component. The app looked fine until you touched it — every
 * modal, select and form field failed while server-rendered pages still loaded,
 * which is how a read-only smoke test walks straight past it.
 *
 * The gate belongs on that route for the same reason it belongs on `/`.
 */

function withPratiqueDriver(): void
{
    config(['auth.driver' => AuthenticationStrategyFactory::DRIVER_PRATIQUE]);

    // The binding is resolved once at boot, so swapping the config alone would
    // leave the previous strategy in place and quietly test the wrong driver.
    app()->forgetInstance(AuthenticationStrategy::class);
    app()->instance(AuthenticationStrategy::class, AuthenticationStrategyFactory::make(
        AuthenticationStrategyFactory::DRIVER_PRATIQUE,
        app()->environment(),
        static fn (): AuthenticationStrategy => app(PratiqueAuthenticationStrategy::class),
    ));
}

afterEach(function (): void {
    // The binding outlives the test, so put it back.
    app()->forgetInstance(AuthenticationStrategy::class);
});

it('refuses a component update that carries no assertion', function (): void {
    withPratiqueDriver();

    $this->withoutMiddleware(VerifyCsrfToken::class)
        ->postJson(route('livewire.update'), ['components' => []])
        ->assertForbidden();
});

it('refuses a component update whose assertion the proxy did not sign', function (): void {
    withPratiqueDriver();

    $this->withoutMiddleware(VerifyCsrfToken::class)
        ->withHeader('Authorization', 'Bearer ' . PratiqueTestHelper::assertionFromForeignKey())
        ->postJson(route('livewire.update'), ['components' => []])
        ->assertForbidden();
});
