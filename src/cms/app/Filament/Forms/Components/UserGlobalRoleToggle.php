<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Enums\Authorization\Role;
use App\Models\User;
use Filament\Forms\Components\Toggle;

use function sprintf;

class UserGlobalRoleToggle extends RoleToggle
{
    public static function makeForUser(string $name, User $user, Role $role): static
    {
        return parent::makeForRole(sprintf('%s.%s', $name, $role->value), $role)
            ->afterStateHydrated(static function (Toggle $component) use ($user, $role): void {
                $hasRole = $user->globalRoles
                    ->pluck('role')
                    ->map(static function ($role): string {
                        return $role->value;
                    })
                    ->contains($role->value);

                $component->state($hasRole);
            });
    }
}
