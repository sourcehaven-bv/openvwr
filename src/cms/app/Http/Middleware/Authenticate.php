<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\RouteName;
use App\Services\Authentication\AuthenticationStrategy;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

use function abort;
use function app;
use function route;

class Authenticate extends Middleware
{
    /**
     * Where an unauthenticated request is sent.
     *
     * The active strategy decides. When it has no login page — the proxy owns the
     * front door — there is nowhere to send them, and building the route anyway
     * would turn "not signed in" into a RouteNotFoundException and a 500. Refuse
     * instead: such a request either bypassed the proxy or arrived before it had
     * authenticated, and both are a 403.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!app(AuthenticationStrategy::class)->hasLoginPage()) {
            abort(403);
        }

        // @codeCoverageIgnoreStart
        return $request->expectsJson() ? null : route(RouteName::FILAMENT_ADMIN_AUTH_LOGIN);
        // @codeCoverageIgnoreEnd
    }
}
