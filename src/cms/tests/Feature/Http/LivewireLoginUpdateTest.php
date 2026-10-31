<?php

declare(strict_types=1);

use App\Filament\Pages\DevLogin;
use App\Filament\Pages\Login;
use App\Mail\Authentication\PasswordLessLoginLink;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Authentication\AuthenticationStrategy;
use App\Services\Authentication\AuthenticationStrategyFactory;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

use function Pest\Livewire\livewire;

/*
 * A login form submits through POST /livewire/update before anyone is logged
 * in. That route runs the strategy's gate only for a strategy without a login
 * page (pratique). Under builtin and dev the gate is Authenticate, which would
 * refuse the very request that logs the user in.
 *
 * livewire() never touches that route: it calls the component directly. So
 * these tests take the component's snapshot and post it to the real route,
 * with the route's real middleware.
 */

function useDriver(string $driver): void
{
    config(['auth.driver' => $driver]);

    // The binding is resolved once at boot, so swapping the config alone would
    // leave the previous strategy in place and quietly test the wrong driver.
    app()->forgetInstance(AuthenticationStrategy::class);
    app()->instance(
        AuthenticationStrategy::class,
        AuthenticationStrategyFactory::make($driver, app()->environment()),
    );
}

/** @param array<string, string> $updates */
function submitThroughUpdateRoute(string $component, array $updates): TestResponse
{
    $snapshot = livewire($component)->snapshot;

    // livewire() logs nobody in, but reset the guard so the request below is
    // unauthenticated whatever the component did while mounting.
    Auth::forgetGuards();

    return test()->withoutMiddleware(VerifyCsrfToken::class)
        ->withHeader('X-Livewire', 'true')
        ->postJson(route('livewire.update'), [
            'components' => [
                [
                    'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                    'updates' => $updates,
                    'calls' => [['path' => '', 'method' => 'authenticate', 'params' => []]],
                ],
            ],
        ]);
}

afterEach(function (): void {
    // The binding outlives the test, so put it back.
    app()->forgetInstance(AuthenticationStrategy::class);
});

it('accepts the builtin login form without a session', function (): void {
    useDriver(AuthenticationStrategyFactory::DRIVER_BUILTIN);
    Mail::fake();

    $user = User::factory()->create();
    $user->organisations()->attach(Organisation::factory()->create());

    submitThroughUpdateRoute(Login::class, ['data.email' => $user->email])
        ->assertOk();

    Mail::assertQueued(PasswordLessLoginLink::class);
});

it('accepts the dev login form without a session', function (): void {
    useDriver(AuthenticationStrategyFactory::DRIVER_DEV);

    $user = User::factory()->create();
    $user->organisations()->attach(Organisation::factory()->create());

    submitThroughUpdateRoute(DevLogin::class, ['data.userId' => $user->id->toString()])
        ->assertOk();

    expect(Auth::id())->toBe($user->id);
});
