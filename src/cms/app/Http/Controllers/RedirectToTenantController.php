<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RouteName;
use App\Facades\Authentication;
use App\Models\Organisation;
use App\Models\OrganisationUserRole;
use App\Services\Authentication\AuthenticationStrategy;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Http\RedirectResponse;
use Throwable;
use Webmozart\Assert\Assert;

use function abort;
use function app;
use function redirect;
use function route;

class RedirectToTenantController
{
    public function __invoke(): RedirectResponse
    {
        try {
            $user = Authentication::user();
        } catch (Throwable) {
            // No login page under this strategy means the proxy is the front
            // door, so there is nowhere to send them. Refuse rather than build a
            // route that does not exist.
            if (!app(AuthenticationStrategy::class)->hasLoginPage()) {
                abort(403);
            }

            return redirect(route(RouteName::FILAMENT_ADMIN_AUTH_LOGIN));
        }

        /** @var OrganisationUserRole|null $organisationRole */
        $organisationRole = $user->organisationRoles()->first();

        if ($organisationRole !== null) {
            return $this->redirectToOrganisation($organisationRole->organisation);
        }

        $organisation = $user->organisations->first();
        if ($organisation === null) {
            abort(403);
        }

        if ($user->globalRoles->isNotEmpty()) {
            return $this->redirectToOrganisation($organisation);
        }

        abort(403);
    }

    private function redirectToOrganisation(Organisation $organisation): RedirectResponse
    {
        $panel = Filament::getCurrentPanel();
        Assert::isInstanceOf($panel, Panel::class);

        $url = $panel->getUrl($organisation);
        Assert::string($url);

        return redirect($url);
    }
}
