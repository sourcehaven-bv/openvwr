<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Enums\Authorization\Role;
use Closure;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;

use function __;
use function sprintf;

class RoleToggle extends Toggle
{
    public static function makeForRole(string $name, Role $role): static
    {
        return parent::make($name)
            ->label(__(sprintf('role.%s', $role->value)))
            ->helperText(__(sprintf('role.descriptions.%s', $role->value)));
    }

    /**
     * Builds the grouped organisation role toggles shared by the user forms.
     *
     * @param Closure(Role): string $nameResolver
     * @param Closure(static, Role): Toggle|null $configure
     *
     * @return array<Section>
     */
    public static function organisationRoleSections(
        bool $includeCpoRoles,
        Closure $nameResolver,
        ?Closure $configure = null,
    ): array {
        $organisationRoleToggleSections = [];

        foreach (Role::organisationRoleGroups($includeCpoRoles) as $organisationRoleGroup) {
            $organisationRoleToggles = [];

            foreach ($organisationRoleGroup as $organisationRole) {
                $organisationRoleToggle = self::makeForRole($nameResolver($organisationRole), $organisationRole);

                $organisationRoleToggles[] = $configure === null
                    ? $organisationRoleToggle
                    : $configure($organisationRoleToggle, $organisationRole);
            }

            $organisationRoleToggleSections[] = Section::make($organisationRoleToggles)->columns();
        }

        return $organisationRoleToggleSections;
    }
}
