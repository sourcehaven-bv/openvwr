<?php

declare(strict_types=1);

namespace App\Providers;

use App\Config\Config;
use App\Facades\Authentication;
use App\Filament\AccessibleButtonComponent;
use App\Filament\LabelColorPalette;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\OnePageLayoutRenderHooks;
use App\Filament\Pages\Manual\Handleiding;
use App\Filament\Pages\Profile;
use App\Filament\SimpleAvatarProvider;
use App\Http\Controllers\HealthController;
use App\Http\Middleware\IPAllowFilter;
use App\Models\Organisation;
use App\Services\Authentication\AuthenticationStrategy;
use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup as FilamentNavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\Support\View\Components\ButtonComponent;
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
use function app;
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
        // Buttons pick their shades through this component; see the class.
        $this->app->bind(ButtonComponent::class, AccessibleButtonComponent::class);

        FilamentColor::register([
            'primary' => self::primaryPalette(),
            ...LabelColorPalette::all(),
        ]);

        FilamentAsset::register([
            Css::make('app', base_path('resources/css/app.css')),
        ]);

        // v3 let these layout components span the full width; v4 dropped that
        // default. Without it every form and infolist, which default to two
        // columns, puts its sections side by side in narrow cards.
        foreach ([Section::class, Fieldset::class, Grid::class] as $layoutComponent) {
            $layoutComponent::configureUsing(
                static fn (Section|Fieldset|Grid $component) => $component->columnSpanFull(),
            );
        }

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
     * The house colour as a v3-style ramp. v4 generates shades in OKLCH, which
     * gives #F84F39 a duller 600 and 700 than production has.
     *
     * @return array<int, string>
     */
    private static function primaryPalette(): array
    {
        return Color::generateV3Palette('#F84F39');
    }

    /**
     * The panel's login page, or null when this driver has none.
     *
     * Both this and the auth middleware come from the active strategy rather
     * than a match here: "how is a request gated" and "how is identity
     * established" are one decision, and splitting them across two files is how
     * they drift apart.
     *
     * @return class-string|null
     */
    private function loginPage(): ?string
    {
        return $this->strategy()->loginPage();
    }

    /** @return array<int, class-string> */
    private function authMiddleware(): array
    {
        return $this->strategy()->panelMiddleware();
    }

    private function strategy(): AuthenticationStrategy
    {
        return app(AuthenticationStrategy::class);
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
    private function manualMenuItem(): Action
    {
        return Action::make('manual')
            ->url(static function (): string {
                $panel = Filament::getCurrentOrDefaultPanel();
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

    private function profileMenuItem(): Action
    {
        return Action::make('profile')
            ->url(static function (): string {
                $panel = Filament::getCurrentOrDefaultPanel();
                Assert::isInstanceOf($panel, Panel::class);

                $route = request()->route();
                Assert::isInstanceOf($route, Route::class);

                try {
                    $tenant = Organisation::where(['slug' => $route->parameter('tenant')])->firstOrFail();
                } catch (ModelNotFoundException) {
                    abort(404);
                }

                return Profile::getUrl(panel: $panel->getId(), tenant: $tenant);
            })
            // Label and icon came from Filament's own profile item as long
            // as this was keyed 'account'; under our own key they are ours
            // to give.
            ->icon('heroicon-o-user-circle')
            ->label(Profile::getLabel());
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

                // v5 builds the profile item of the user menu through
                // Filament::getProfileUrl(), which resolves the panel's
                // `auth.profile` route. Our profile is a tenant page and
                // registers as `pages.profile`, so that name would not exist and
                // rendering any page with a user menu would fail. This alias
                // gives the name something to point at without moving the page.
                RouteFacade::redirect('/auth-profile', '/profile')->name('auth.profile');
            })
            ->colors([
                'primary' => self::primaryPalette(),
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
                // Keyed 'profile' on purpose. v5 adds a profile item of its own
                // when the menu has none, and that one links through
                // getProfileUrl() to the panel's auth profile route, which a
                // tenant page like ours never registers. Claiming the key keeps
                // our own tenant-aware url instead.
                'profile' => $this->profileMenuItem(),
                'manual' => $this->manualMenuItem(),
            ])
            ->maxContentWidth('screen-2xl')
            ->sidebarWidth('25rem')
            ->favicon(asset('favicon.ico'));
    }
}
