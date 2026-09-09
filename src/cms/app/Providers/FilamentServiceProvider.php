<?php

declare(strict_types=1);

namespace App\Providers;

use App\Config\Config;
use App\Facades\Authentication;
use App\Filament\LabelColorPalette;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\OnePageLayoutRenderHooks;
use App\Filament\Pages\DevLogin;
use App\Filament\Pages\Login;
use App\Filament\Pages\Manual\Handleiding;
use App\Filament\Pages\Profile;
use App\Filament\SimpleAvatarProvider;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\EnforceOneTimePassword;
use App\Http\Middleware\IPAllowFilter;
use App\Http\Middleware\VerifyPratiqueAssertion;
use App\Models\Organisation;
use App\Services\Authentication\AuthenticationStrategyFactory;
use Exception;
use Filament\Facades\Filament;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup as FilamentNavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Route;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;
use Spatie\Csp\AddCspHeaders;
use Webmozart\Assert\Assert;

use function __;
use function abort;
use function app_path;
use function asset;
use function base_path;
use function request;
use function view;

class FilamentServiceProvider extends PanelProvider
{
    public function boot(): void
    {
        // Ensure primary color is set globally, including non-panel pages.
        // The label palette is registered alongside it so label badges render
        // outside the panel too.
        FilamentColor::register([
            'primary' => '#F84F39',
            ...LabelColorPalette::all(),
        ]);

        FilamentAsset::register([
            Css::make('app', base_path('resources/css/app.css')),
        ]);

        // BODY_START rather than a topbar hook: the banner spans the full width
        // above the panel chrome, and renders on the login page too, where an
        // "this is a demo environment" notice matters most.
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            static function (): View {
                return view('filament.banner');
            },
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_START,
            static function (): View {
                return view('filament.topbar.organisation_name');
            },
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            static function (): View {
                return view('filament.footer');
            },
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            static function (): View {
                return view('filament.version_meta');
            },
        );

        OnePageLayoutRenderHooks::register();
    }

    /**
     * The login page for the active auth driver, or null when the app has none.
     *
     * Under `pratique` the proxy owns login entirely: registering a login page
     * here would give an unauthenticated visitor somewhere to land inside the app
     * instead of being bounced to the proxy, and the assertion middleware would
     * reject it anyway.
     *
     * Under `dev` it is the credential-free user picker — selected here rather
     * than registered unconditionally, because a page that bypasses
     * authentication should not exist on the panel unless that driver is
     * deliberately in use.
     *
     * @return class-string|null
     */
    private function loginPage(): ?string
    {
        return match ($this->authDriver()) {
            AuthenticationStrategyFactory::DRIVER_PRATIQUE => null,
            AuthenticationStrategyFactory::DRIVER_DEV => DevLogin::class,
            default => Login::class,
        };
    }

    /**
     * Auth middleware for the active driver.
     *
     * Under `pratique` the proxy has already authenticated the user, so the
     * session-based gate is replaced wholesale by assertion verification — leaving
     * Filament's Authenticate in place would look for a session that this driver
     * never creates. The OTP gate goes too: the second factor is the proxy's
     * concern there, not this app's.
     *
     * The dev driver keeps the session gate but skips OTP: it is a
     * credential-free login, so a second factor on top would be theatre, and
     * enrolling one would block every local login behind an authenticator app.
     *
     * @return array<int, class-string>
     */
    private function authMiddleware(): array
    {
        return match ($this->authDriver()) {
            AuthenticationStrategyFactory::DRIVER_PRATIQUE => [VerifyPratiqueAssertion::class],
            AuthenticationStrategyFactory::DRIVER_DEV => [Authenticate::class],
            default => [
                Authenticate::class,
                EnforceOneTimePassword::class,
            ],
        };
    }

    private function authDriver(): string
    {
        return Config::string('auth.driver', AuthenticationStrategyFactory::DRIVER_BUILTIN);
    }

    /**
     * The brand lockup shown in the sidebar header and above the login card.
     *
     * Rendered to an Htmlable rather than passed as an image path: a path
     * renders the bare mark, and the top-left needs the wordmark beside it to
     * name the application. Filament only skips its own <img> wrapper for
     * Htmlable, so the view is rendered here rather than returned.
     */
    private function brandLogo(): Htmlable
    {
        return new HtmlString(view('filament.brand.logo')->render());
    }

    /**
     * The manual, which lives in the panel itself so it can follow the same
     * feature flags as the rest of the interface.
     */
    private function manualMenuItem(): MenuItem
    {
        return MenuItem::make()
            ->url(static function (): string {
                $panel = Filament::getCurrentPanel();
                Assert::isInstanceOf($panel, Panel::class);

                $route = request()->route();
                Assert::isInstanceOf($route, Route::class);

                try {
                    $tenant = Organisation::where(['slug' => $route->parameter('tenant')])->firstOrFail();
                } catch (ModelNotFoundException) {
                    abort(404);
                }

                return Handleiding::getUrl(panel: $panel->getId(), tenant: $tenant);
            })
            ->icon('heroicon-o-book-open')
            ->label(__('general.manual'));
    }

    /**
     * @throws Exception
     */
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->font('Inter', asset('fonts/inter.css'), LocalFontProvider::class)
            ->brandName(Config::string('app.name'))
            ->brandLogo($this->brandLogo(...))
            // Filament boxes the logo at this height; the default 1.5rem would
            // clip the mark.
            ->brandLogoHeight('2rem')
            ->login($this->loginPage())
            ->profile(Profile::class)
            ->routes(static function (): void {
                RouteFacade::get('/health', HealthController::class);
                RouteFacade::get('/up', HealthController::class . '@up');
            })
            ->colors([
                'primary' => '#F84F39',
            ])
            ->defaultAvatarProvider(SimpleAvatarProvider::class)
            ->unsavedChangesAlerts()
            ->navigationGroups([
                FilamentNavigationGroup::make()
                    ->label(__(NavigationGroup::REGISTERS->value))
                    ->collapsible(false),
                FilamentNavigationGroup::make()
                    ->label(__(NavigationGroup::DPIA->value))
                    ->collapsible(false),
                FilamentNavigationGroup::make()
                    ->label(__(NavigationGroup::MANAGEMENT->value))
                    ->collapsible(false),
                FilamentNavigationGroup::make()
                    ->label(__(NavigationGroup::OVERVIEWS->value))
                    ->collapsible(false),
                FilamentNavigationGroup::make()
                    ->label(__(NavigationGroup::ORGANISATION->value))
                    ->collapsible(false),
                FilamentNavigationGroup::make()
                    ->label(__(NavigationGroup::FUNCTIONAL_MANAGEMENT->value)),
                FilamentNavigationGroup::make()
                    ->label(__(NavigationGroup::LOOKUP_LISTS->value)),
            ])
            ->tenant(Organisation::class, 'slug', 'organisation')
            ->tenantMenu(static function (): bool {
                return Authentication::user()->organisations->count() > 1;
            })
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->databaseNotifications()
            ->databaseNotificationsPolling(null)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                AddCspHeaders::class,
            ])
            ->authMiddleware($this->authMiddleware())
            ->tenantMiddleware([
                IPAllowFilter::class,
            ], isPersistent: true)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->userMenuItems([
                'account' => MenuItem::make()
                    ->url(static function (): string {
                        $panel = Filament::getCurrentPanel();
                        Assert::isInstanceOf($panel, Panel::class);

                        $route = request()->route();
                        Assert::isInstanceOf($route, Route::class);

                        try {
                            $tenant = Organisation::where(['slug' => $route->parameter('tenant')])->firstOrFail();
                        } catch (ModelNotFoundException) {
                            abort(404);
                        }

                        return Profile::getUrl(panel: $panel->getId(), tenant: $tenant);
                    }),
                'manual' => $this->manualMenuItem(),
            ])
            ->maxContentWidth('screen-2xl')
            ->sidebarWidth('25rem')
            ->favicon(asset('favicon.ico'));
    }
}
