<?php

declare(strict_types=1);

namespace App\Services\Authentication;

use App\Collections\OrganisationUserRoleCollection;
use App\Collections\UserGlobalRoleCollection;
use App\Models\Organisation;
use App\Models\Principal;
use App\Models\User;
use Filament\Facades\Filament;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * The application's own auth: passwordless magic link + TOTP, on Laravel's
 * session guard, with the active tenant resolved from the URL by Filament.
 *
 * This is the historical behaviour, moved here unchanged when the strategy seam
 * was extracted.
 */
class BuiltinAuthenticationStrategy implements AuthenticationStrategy
{
    private ?Principal $principal = null;

    /**
     * @throws InvalidArgumentException
     */
    public function organisation(): Organisation
    {
        $organisation = Filament::getTenant();
        Assert::isInstanceOf($organisation, Organisation::class);

        return $organisation;
    }

    public function principal(): Principal
    {
        if ($this->principal === null) {
            $roles = [];

            foreach ($this->getGlobalRoles() as $globalRole) {
                $roles[] = $globalRole->role;
            }

            foreach ($this->getOrganisationRoles() as $organisationRole) {
                $roles[] = $organisationRole->role;
            }

            $this->principal = new Principal($roles);
        }

        return $this->principal;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function user(): User
    {
        $user = Filament::auth()->user();
        Assert::isInstanceOf($user, User::class);

        return $user;
    }

    private function getGlobalRoles(): UserGlobalRoleCollection
    {
        try {
            return $this->user()->globalRoles;
        } catch (InvalidArgumentException) {
            return new UserGlobalRoleCollection();
        }
    }

    private function getOrganisationRoles(): OrganisationUserRoleCollection
    {
        try {
            $organisationUserRoles = $this->user()
                ->organisationRoles()
                ->where(['organisation_id' => $this->organisation()->id])
                ->get();
            Assert::isInstanceOf($organisationUserRoles, OrganisationUserRoleCollection::class);
        } catch (InvalidArgumentException) {
            $organisationUserRoles = new OrganisationUserRoleCollection();
        }

        return $organisationUserRoles;
    }
}
