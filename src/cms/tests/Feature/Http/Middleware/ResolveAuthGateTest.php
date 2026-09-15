<?php

declare(strict_types=1);

use App\Http\Middleware\ResolveAuthGate;
use App\Services\Authentication\AuthenticationStrategy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\Helpers\NoLoginPageAuthenticationStrategy;
use Tests\Helpers\RecordingAuthenticationStrategy;

/*
 * Routes are registered once and cached, so the gate cannot be named at
 * definition time without freezing whichever driver was bound at boot. This
 * resolves the strategy per request instead.
 */

beforeEach(function (): void {
    // Static, so it survives the test that set it.
    RecordingAuthenticationStrategy::$ran = false;
});

afterEach(function (): void {
    // The binding outlives the test, so put it back.
    app()->forgetInstance(AuthenticationStrategy::class);
    RecordingAuthenticationStrategy::$ran = false;
});

function passThrough(): Response
{
    $gate = new ResolveAuthGate(app());

    return $gate->handle(
        Request::create('/'),
        static fn (): Response => new Response('reached'),
    );
}

it('passes the request straight through when the strategy gates nothing', function (): void {
    app()->forgetInstance(AuthenticationStrategy::class);
    app()->instance(AuthenticationStrategy::class, new NoLoginPageAuthenticationStrategy());

    expect(passThrough()->getContent())->toBe('reached');
});

it('runs the middleware the strategy names', function (): void {
    app()->forgetInstance(AuthenticationStrategy::class);
    app()->instance(AuthenticationStrategy::class, new RecordingAuthenticationStrategy());

    expect(passThrough()->getContent())->toBe('reached')
        ->and(RecordingAuthenticationStrategy::$ran)->toBeTrue();
});
