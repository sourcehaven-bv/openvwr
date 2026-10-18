<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\OrganisationRelationManager;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource\UserResourceForm;
use App\Filament\Resources\UserResource\UserResourceInfolist;
use App\Filament\Resources\UserResource\UserResourceTable;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function __;

class UserResource extends Resource
{
    protected static bool $isScopedToTenant = false;
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::FUNCTIONAL_MANAGEMENT->value);
    }

    public static function form(Form $form): Form
    {
        return UserResourceForm::form($form);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return UserResourceInfolist::infolist($infolist);
    }

    public static function table(Table $table): Table
    {
        return UserResourceTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            OrganisationRelationManager::class,
        ];
    }

    /**
     * Verwijdert de soft-delete-scope, zodat TrashedFilter in de tabel kan
     * schakelen tussen actieve en verwijderde gebruikers. Zonder deze override
     * filtert het basisquery verwijderde rijen er al uit en heeft het filter
     * geen effect. Standaard toont de tabel nog steeds alleen actieve
     * gebruikers; TrashedFilter bepaalt de rest.
     *
     * @return Builder<User>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            'view' => ViewUser::route('/{record}'),
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
}
