<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Models\User;
use App\Models\UserGlobalRole;

use function array_filter;
use function array_map;
use function array_rand;
use function array_values;
use function in_array;

class PermissionTestHelper
{
    /**
     * @param array<Permission> $permissions
     */
    public static function give(User $user, array $permissions): void
    {
        // A role another user already holds would hand this user that user's
        // permissions too: services cache the role map before it is replaced.
        $taken = UserGlobalRole::query()->pluck('role')->all();
        $roles = array_values(array_filter(
            Role::cases(),
            static fn (Role $role): bool => !in_array($role, $taken, true),
        ));
        $roles = $roles === [] ? Role::cases() : $roles;
        $role = $roles[array_rand($roles)];

        ConfigTestHelper::set('permissions.roles_and_permissions', [
            $role->value => self::getPermisisionValues($permissions),
        ]);

        UserGlobalRole::factory()
            ->for($user)
            ->create([
                'role' => $role,
            ]);
    }

    /**
     * @param array<Permission> $permissions
     *
     * @return array<string>
     */
    private static function getPermisisionValues(array $permissions): array
    {
        return array_map(static function (Permission $permission): string {
            return $permission->value;
        }, $permissions);
    }
}
