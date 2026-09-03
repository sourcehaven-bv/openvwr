<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\RouteName;
use App\Facades\Authentication;
use App\Facades\Otp;
use App\Filament\Pages\Profile;
use Closure;
use Exception;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Webmozart\Assert\Assert;

use function is_string;
use function redirect;
use function request;
use function route;
use function sprintf;

class EnforceOneTimePassword
{
    /**
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $route = $request->route();
        Assert::isInstanceOf($route, Route::class);

        $tenantId = $route->parameter('tenant');
        if (!is_string($tenantId)) {
            return $next($request);
        }

        $user = Authentication::user();
        if (Otp::hasOtpConfirmed($user)) {
            return $this->getOtpConfirmationResponse($request, $next, $tenantId);
        }

        return $this->getProfilePageRedirectResponse($request, $next, $tenantId);
    }

    private function getProfilePageRouteName(): string
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        Assert::isInstanceOf($panel, Panel::class);

        return sprintf('filament.%s.pages.%s', $panel->getId(), Profile::getSlug());
    }

    private function getOtpConfirmationResponse(Request $request, Closure $next, string $tenantId): mixed
    {
        if (Otp::hasValidSession()) {
            return $next($request);
        }

        return redirect(route(RouteName::TWO_FACTOR_AUTHENTICATION_REQUEST, [
            'tenant' => $tenantId,
            'next' => request()->getRequestUri(),
        ]));
    }

    private function getProfilePageRedirectResponse(Request $request, Closure $next, string $tenantId): mixed
    {
        $profilePageRouteName = $this->getProfilePageRouteName();
        if ($request->routeIs($profilePageRouteName)) {
            return $next($request);
        }

        return redirect()->route($profilePageRouteName, ['tenant' => $tenantId]);
    }
}
