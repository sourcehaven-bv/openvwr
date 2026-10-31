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
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

use function __;

class UserResource extends Resource
{
    protected static bool $isScopedToTenant = false;
    protected static ?string $model = User::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::FUNCTIONAL_MANAGEMENT->value);
    }

    public static function form(Schema $schema): Schema
    {
        return UserResourceForm::form($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserResourceInfolist::infolist($schema);
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
