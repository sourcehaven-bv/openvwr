<?php

declare(strict_types=1);

namespace App\Providers;

use App\Config\Config;
use App\Http\Middleware\ResolveAuthGate;
use App\Routing\UrlGenerator;
use App\Services\StringService;
use App\Services\Virusscanner\Virusscanner;
use App\Services\Virusscanner\VirusscannerManager;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypesInterface;
use Webmozart\Assert\Assert;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Date::use(CarbonImmutable::class);

        $this->gateLivewireUpdates();

        // phpcs:ignore SlevomatCodingStandard.Functions.StaticClosure
        Str::macro('mailSafe', function (?string $value): ?string {
            return StringService::mailSafe($value);
        });

        // phpcs:ignore SlevomatCodingStandard.Functions.StaticClosure
        Str::macro('toSingleLineEscapedString', function (?string $value, string $whenEmpty = ''): string {
            return StringService::toSingleLineEscapedString($value, $whenEmpty);
        });
    }

    /**
     * Put Livewire's update endpoint behind the active strategy's gate.
     *
     * Livewire registers `POST /livewire/update` itself, on the `web` group
     * alone. That group starts a session and checks CSRF but establishes no
     * identity, so under a driver where the proxy authenticates — and the app
     * never creates a session of its own — every component update arrives
     * unauthenticated: no user, no tenant, and Livewire answers 404 because it
     * cannot resolve the component for a tenant that is not set.
     *
     * The visible effect is an application that looks fine until you touch it:
     * server-rendered pages load, while every modal, select and form field
     * fails. Read-only checks walk straight past it.
     *
     * ResolveAuthGate resolves the strategy per request rather than at
     * registration, which matters here because this route is registered once and
     * cached like any other.
     */
    private function gateLivewireUpdates(): void
    {
        // $handle is Livewire's [class, method] pair, not a closure, so it is
        // typed as the array-or-callable that Route::post accepts.
        Livewire::setUpdateRoute(
            /** @param array{0: class-string, 1: string}|callable $handle */
            static fn (array|callable $handle): Route => RouteFacade::post('/livewire/update', $handle)
                ->middleware(['web', ResolveAuthGate::class]),
        );
    }

    public function register(): void
    {
        $this->app->singleton('url', static function (Application $app): UrlGenerator {
            /** @var Request $request */
            $request = $app->rebinding('request', static function (Application $app, Request $request): void {
                $app['url']->setRequest($request);
            });

            return new UrlGenerator($app['router']->getRoutes(), $request, Config::stringOrNull('app.asset_url'));
        });

        $this->app->bind(Virusscanner::class, static function (Application $application): Virusscanner {
            /** @var VirusscannerManager $virusscannerManager */
            $virusscannerManager = $application->get(VirusscannerManager::class);
            $virusscanner = $virusscannerManager->driver(Config::string('virusscanner.default'));
            Assert::isInstanceOf($virusscanner, Virusscanner::class);

            return $virusscanner;
        });

        $this->app->bind(MimeTypesInterface::class, MimeTypes::class);
    }
}
