<?php

declare(strict_types=1);

use App\Services\Authentication\AuthenticationStrategyFactory;
use App\Services\Authentication\BuiltinAuthenticationStrategy;
use App\Services\Authentication\DevAuthenticationStrategy;

it('builds the builtin strategy', function (): void {
    expect(AuthenticationStrategyFactory::make('builtin', 'production'))
        ->toBeInstanceOf(BuiltinAuthenticationStrategy::class);
});

it('builds the dev strategy in permitted environments', function (string $environment): void {
    expect(AuthenticationStrategyFactory::make('dev', $environment))
        ->toBeInstanceOf(DevAuthenticationStrategy::class);
})->with(['local', 'testing']);

/*
 * The dev strategy logs anyone in without credentials, so reaching a deployed
 * environment with it configured would be a full authentication bypass. These
 * are the tests that make that impossible, so they are the point of this file.
 */
it('refuses to build the dev strategy outside local and testing', function (string $environment): void {
    AuthenticationStrategyFactory::make('dev', $environment);
})->with([
    'production',
    'staging',
    'acceptance',
])->throws(RuntimeException::class, 'cannot run in the');

it('reports whether dev is allowed per environment', function (): void {
    expect(AuthenticationStrategyFactory::devAllowedIn('local'))->toBeTrue()
        ->and(AuthenticationStrategyFactory::devAllowedIn('testing'))->toBeTrue()
        ->and(AuthenticationStrategyFactory::devAllowedIn('production'))->toBeFalse()
        ->and(AuthenticationStrategyFactory::devAllowedIn('staging'))->toBeFalse();
});

/*
 * An unknown driver must stop the app rather than quietly falling back to some
 * default — a typo in AUTH_DRIVER should never silently change how requests are
 * authenticated.
 */
it('rejects an unknown driver', function (): void {
    AuthenticationStrategyFactory::make('nope', 'local');
})->throws(RuntimeException::class, 'Unknown auth driver');
