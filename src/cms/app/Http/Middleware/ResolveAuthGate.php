<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Authentication\AuthenticationStrategy;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

/**
 * Applies whatever middleware the active strategy uses to gate a request.
 *
 * A route can only name middleware classes, and route definitions are registered
 * once (and cached) — so naming the strategy's list directly would freeze
 * whichever strategy happened to be bound at boot. This resolves the list per
 * request instead, which keeps a route on the `web` group gated the same way the
 * panel is without duplicating the driver decision here.
 */
class ResolveAuthGate
{
    public function __construct(
        private readonly Application $app,
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $gate = $this->app->make(AuthenticationStrategy::class)->panelMiddleware();

        if ($gate === []) {
            return $next($request);
        }

        return (new Pipeline($this->app))
            ->send($request)
            ->through($gate)
            ->then(static fn (Request $passed): mixed => $next($passed));
    }
}
