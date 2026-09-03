<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Enums\Authorization\Role;
use App\Models\OrganisationUserRole;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Webmozart\Assert\Assert;

use function __;
use function array_key_exists;
use function array_keys;
use function sprintf;

class OrganisationUserRolesRepeater extends Repeater
{
    public static function makeForUser(string $name, User $user): static
    {
        return parent::make($name)
            ->label('')
            ->addActionLabel(__('user.organisation_roles_add'))
            ->schema([
                Select::make('organisation_id')
                    ->label(__('organisation.model_singular'))
                    ->required()
                    ->distinct()
                    ->options(self::getOrganisationOptions($user))
                    ->in(array_keys(self::getOrganisationOptions($user)))
                    ->columnSpan(2),

                ...self::getOrganisationRoleToggles($name),
            ])
            ->afterStateHydrated(static function (Repeater $component) use ($user): void {
                $component->state(self::getOrganisationUserRoles($user));
            })
            ->columns(2)
            ->reorderable(false);
    }

    /**
     * @return array<string, string>
     */
    private static function getOrganisationOptions(User $user): array
    {
        $organisationOptions = $user->organisations()
            ->pluck('name', 'id')
            ->toArray();

        Assert::isMap($organisationOptions);
        Assert::allString($organisationOptions);

        return $organisationOptions;
    }

    /**
     * @return array<Section>
     */
    private static function getOrganisationRoleToggles(string $name): array
    {
        return RoleToggle::organisationRoleSections(
            true,
            static function (Role $organisationRole) use ($name): string {
                return sprintf('%s.%s', $name, $organisationRole->value);
            },
        );
    }

    /**
     * @return array<string, array{organisation_id: string, organisation_user_roles: non-empty-array<value-of<Role>, true>}>
     */
    private static function getOrganisationUserRoles(User $user): array
    {
        $organisationUserRoles = [];
        /** @var OrganisationUserRole $organisationUserRole */
        foreach ($user->organisationRoles as $organisationUserRole) {
            $organisationId = $organisationUserRole->organisation_id->toString();
            if (!array_key_exists($organisationId, $organisationUserRoles)) {
                $organisationUserRoles[$organisationId]['organisation_id'] = $organisationId;
            }

            $organisationUserRoles[$organisationId]['organisation_user_roles'][$organisationUserRole->role->value] = true;
        }

        return $organisationUserRoles;
    }
}
