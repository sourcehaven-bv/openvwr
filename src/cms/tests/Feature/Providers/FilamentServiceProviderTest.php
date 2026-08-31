<?php

declare(strict_types=1);

use App\Filament\Pages\DevLogin;
use App\Filament\Pages\Login;
use App\Http\Middleware\EnforceOneTimePassword;
use App\Providers\FilamentServiceProvider;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Panel;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*
 * The panel's login page and auth middleware depend on the active driver, and
 * those branches only run at panel-build time. Building the panel directly is
 * what exercises them — the test environment itself stays on `builtin`.
 */

function buildPanel(string $driver): Panel
{
    config(['auth.driver' => $driver]);

    $provider = new FilamentServiceProvider(app());

    return $provider->panel(Panel::make());
}

it('uses the passwordless login page under the builtin driver', function (): void {
    $panel = buildPanel('builtin');

    expect($panel->getLoginRouteAction())->toBe(Login::class);
});

it('uses the user picker under the dev driver', function (): void {
    $panel = buildPanel('dev');

    expect($panel->getLoginRouteAction())->toBe(DevLogin::class);
});

it('enforces the one-time password gate under the builtin driver', function (): void {
    $panel = buildPanel('builtin');

    expect($panel->getAuthMiddleware())
        ->toContain(Authenticate::class)
        ->toContain(EnforceOneTimePassword::class);
});

/*
 * The dev driver is a credential-free login, so a second factor on top of it
 * would be theatre — and enrolling one would put every local login behind an
 * authenticator app.
 */
it('skips the one-time password gate under the dev driver', function (): void {
    $panel = buildPanel('dev');

    expect($panel->getAuthMiddleware())
        ->toContain(Authenticate::class)
        ->not->toContain(EnforceOneTimePassword::class);
});

/*
 * The manual is reached from the user menu, whose url is resolved lazily against
 * the tenant in the current route. A slug that no longer resolves to an
 * organisation must abort rather than build a url for a tenant that is not there.
 */
it('aborts the manual menu link when the route tenant does not exist', function (): void {
    $panel = buildPanel('builtin');
    Filament::setCurrentPanel($panel);

    $route = new Route(['GET'], '/{tenant}/handleiding', static fn (): string => '');
    $route->parameters = ['tenant' => 'bestaat-niet'];
    request()->setRouteResolver(static fn (): Route => $route);

    $panel->getUserMenuItems()['manual']->getUrl();
})->throws(NotFoundHttpException::class);
