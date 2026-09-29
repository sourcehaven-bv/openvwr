<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RouteName;
use App\Facades\Authentication;
use App\Models\Organisation;
use App\Models\OrganisationUserRole;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Http\RedirectResponse;
use Throwable;
use Webmozart\Assert\Assert;

use function abort;
use function redirect;
use function route;

class RedirectToTenantController
{
    public function __invoke(): RedirectResponse
    {
        try {
            $user = Authentication::user();
        } catch (Throwable) {
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
        $panel = Filament::getCurrentOrDefaultPanel();
        Assert::isInstanceOf($panel, Panel::class);

        $url = $panel->getUrl($organisation);
        Assert::string($url);

        return redirect($url);
    }
}
