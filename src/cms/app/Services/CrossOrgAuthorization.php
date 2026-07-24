<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\User;

use function in_array;

/**
 * Evaluates a user's permissions in an arbitrary organisation, not just the active tenant.
 * Needed for cross-org copy: to know whether a user may write into the target organisation
 * we must resolve the roles they hold *there* (global roles + that org's roles), rather than
 * the roles resolved for the currently active tenant.
 */
readonly class CrossOrgAuthorization
{
    /**
     * @param array<value-of<Role>, array<value-of<Permission>>> $rolesAndPermissions
     */
    public function __construct(
        private array $rolesAndPermissions,
    ) {
    }

    public function userHasPermissionInOrganisation(User $user, Organisation $organisation, Permission $permission): bool
    {
        foreach ($this->rolesInOrganisation($user, $organisation) as $role) {
            $permissions = $this->rolesAndPermissions[$role->value] ?? null;

            if ($permissions !== null && in_array($permission->value, $permissions, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<Role>
     */
    private function rolesInOrganisation(User $user, Organisation $organisation): array
    {
        $roles = [];

        foreach ($user->globalRoles as $globalRole) {
            $roles[] = $globalRole->role;
        }

        $organisationRoles = $user->organisationRoles()
            ->where('organisation_id', $organisation->id->toString())
            ->get();

        foreach ($organisationRoles as $organisationRole) {
            $roles[] = $organisationRole->role;
        }

        return $roles;
    }

    /**
     * The organisations (other than the source) the user may copy content into.
     *
     * @return list<Organisation>
     */
    public function copyTargetsFor(User $user, Organisation $sourceOrganisation): array
    {
        $targets = [];

        foreach ($user->organisations as $organisation) {
            if ($organisation->id->equals($sourceOrganisation->id)) {
                continue;
            }

            if ($this->userHasPermissionInOrganisation($user, $organisation, Permission::CORE_ENTITY_IMPORT)) {
                $targets[] = $organisation;
            }
        }

        return $targets;
    }
}
