<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationUserResource;

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Facades\Authorization;
use App\Models\OrganisationUserRole;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

use function __;
use function sprintf;

class OrganisationUserResourceForm
{
    public const string FIELD_USER_GLOBAL_ROLES = 'user_global_roles';
    public const string FIELD_ORGANISATION_USER_ROLES = 'organisation_user_roles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('user.model_singular'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('general.name'))
                            ->disabled(),

                        TextInput::make('email')
                            ->label(__('user.email'))
                            ->disabled(),
                    ]),
                Section::make(__('user.organisation_roles'))
                    ->schema(self::getOrganisationRoleToggles()),
            ]);
    }

    /**
     * @return array<Section>
     */
    private static function getOrganisationRoleToggles(): array
    {
        $organisationRoleToggleSections = [];
        $includeCpoRoles = Authorization::hasPermission(Permission::USER_ROLE_ORGANISATION_CPO_MANAGE);

        foreach (Role::organisationRoleGroups($includeCpoRoles) as $organisationRoleGroup) {
            $organisationRoleToggles = [];

            foreach ($organisationRoleGroup as $organisationRole) {
                $organisationRoleToggles[] = Toggle::make($organisationRole->value)
                    ->label(__(sprintf('role.%s', $organisationRole->value)))
                    ->formatStateUsing(static function (User $record) use ($organisationRole): bool {
                        $organisationRoles = $record->organisationRoles
                            ->map(static function (OrganisationUserRole $organisationUserRole): Role {
                                return $organisationUserRole->role;
                            });

                        return $organisationRoles->contains($organisationRole);
                    });
            }

            $organisationRoleToggleSections[] = Section::make($organisationRoleToggles)->columns();
        }

        return $organisationRoleToggleSections;
    }
}
