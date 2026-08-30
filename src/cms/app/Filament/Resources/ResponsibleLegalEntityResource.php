<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Resources\ResponsibleLegalEntityResource\Pages\CreateResponsibleLegalEntity;
use App\Filament\Resources\ResponsibleLegalEntityResource\Pages\EditResponsibleLegalEntity;
use App\Filament\Resources\ResponsibleLegalEntityResource\Pages\ViewResponsibleLegalEntity;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\Resources\ResponsibleLegalEntityResource\Pages;
use App\Filament\Resources\ResponsibleLegalEntityResource\Pages\ListResponsibleLegalEnties;
use App\Filament\Resources\ResponsibleLegalEntityResource\ResponsibleLegalEntityResourceForm;
use App\Filament\Resources\ResponsibleLegalEntityResource\ResponsibleLegalEntityResourceInfolist;
use App\Filament\Resources\ResponsibleLegalEntityResource\ResponsibleLegalEntityResourceTable;
use App\Models\ResponsibleLegalEntity;
use Filament\Resources\Resource;
use Filament\Tables;

use function __;

class ResponsibleLegalEntityResource extends Resource
{
    protected static ?string $model = ResponsibleLegalEntity::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 5;
    protected static bool $isScopedToTenant = false;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::FUNCTIONAL_MANAGEMENT->value);
    }

    public static function form(Schema $schema): Schema
    {
        return ResponsibleLegalEntityResourceForm::form($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ResponsibleLegalEntityResourceInfolist::infolist($schema);
    }

    public static function table(Table $table): Table
    {
        return ResponsibleLegalEntityResourceTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResponsibleLegalEnties::route('/'),
            'create' => CreateResponsibleLegalEntity::route('/create'),
            'edit' => EditResponsibleLegalEntity::route('/{record}/edit'),
            'view' => ViewResponsibleLegalEntity::route('/{record}'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('responsible_legal_entity.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('responsible_legal_entity.model_plural');
    }
}
