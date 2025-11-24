<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Facades\Authorization;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\Resources\OrganisationUserResource\OrganisationUserResourceForm;
use App\Filament\Resources\OrganisationUserResource\OrganisationUserResourceInfolist;
use App\Filament\Resources\OrganisationUserResource\OrganisationUserResourceTable;
use App\Filament\Resources\OrganisationUserResource\Pages\CreateOrganisationUser;
use App\Filament\Resources\OrganisationUserResource\Pages\EditOrganisationUser;
use App\Filament\Resources\OrganisationUserResource\Pages\ListOrganisationUsers;
use App\Filament\Resources\OrganisationUserResource\Pages\ViewOrganisationUser;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use function __;

class OrganisationUserResource extends Resource
{
    protected static bool $isScopedToTenant = true;
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?int $navigationSort = 1;
    protected static ?string $tenantOwnershipRelationshipName = 'organisations';

    public static function can(string $action, ?Model $record = null): bool
    {
        return Authorization::hasPermission(Permission::USER_ROLE_ORGANISATION_MANAGE);
    }

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::ORGANISATION->value);
    }

    public static function form(Form $form): Form
    {
        return OrganisationUserResourceForm::form($form);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return OrganisationUserResourceInfolist::infolist($infolist);
    }

    public static function table(Table $table): Table
    {
        return OrganisationUserResourceTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganisationUsers::route('/'),
            'create' => CreateOrganisationUser::route('/create'),
            'edit' => EditOrganisationUser::route('/{record}/edit'),
            'view' => ViewOrganisationUser::route('/{record}'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('user.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('user.model_plural');
    }

    /**
     * @return Collection<int, Role>
     */
    public static function getOrganisationUserRoleOptions(): Collection
    {
        /** @var Collection<int, Role> $organisationUserRoleOptions */
        $organisationUserRoleOptions = new Collection([
            Role::INPUT_PROCESSOR,
            Role::INPUT_PROCESSOR_DATABREACH,
            Role::PRIVACY_OFFICER,
            Role::COUNSELOR,
            Role::DATA_PROTECTION_OFFICIAL,
        ]);

        if (Authorization::hasPermission(Permission::USER_ROLE_ORGANISATION_CPO_MANAGE)) {
            $organisationUserRoleOptions->prepend(Role::CHIEF_PRIVACY_OFFICER);
            $organisationUserRoleOptions->prepend(Role::MANDATE_HOLDER);
        }

        return $organisationUserRoleOptions;
    }
}
