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

use function array_key_exists;

/**
 * The application's own auth: passwordless magic link + TOTP, on Laravel's
 * session guard, with the active tenant resolved from the URL by Filament.
 *
 * This is the historical behaviour, moved here unchanged when the strategy seam
 * was extracted.
 */
class BuiltinAuthenticationStrategy implements AuthenticationStrategy
{
    /**
     * Memoised roles, keyed by "<user>:<organisation>".
     *
     * Roles are scoped to the ACTIVE organisation, and both the user and that
     * organisation can change within a single request (a tenant switch, or the
     * test helper re-authenticating). This object is a container singleton, so an
     * unkeyed cache would let a role held in one organisation apply in another —
     * a privilege escalation, not just a stale read. The key makes that
     * impossible while keeping the per-question caching the roles lookup needs.
     *
     * @var array<string, Principal>
     */
    private array $principals = [];

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
        $key = $this->principalCacheKey();

        if (!array_key_exists($key, $this->principals)) {
            $roles = [];

            foreach ($this->getGlobalRoles() as $globalRole) {
                $roles[] = $globalRole->role;
            }

            foreach ($this->getOrganisationRoles() as $organisationRole) {
                $roles[] = $organisationRole->role;
            }

            $this->principals[$key] = new Principal($roles);
        }

        return $this->principals[$key];
    }

    /**
     * Identifies whose roles, in which organisation, a cached Principal describes.
     * Either side being absent is its own distinct key, so an unauthenticated or
     * tenant-less lookup can never read a populated entry.
     */
    private function principalCacheKey(): string
    {
        try {
            $userKey = $this->user()->id->toString();
        } catch (InvalidArgumentException) {
            $userKey = 'no-user';
        }

        try {
            $organisationKey = $this->organisation()->id->toString();
        } catch (InvalidArgumentException) {
            $organisationKey = 'no-organisation';
        }

        return $userKey . ':' . $organisationKey;
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
