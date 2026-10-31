<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\AvgProcessorProcessingRecordRelationManager;
use App\Filament\RelationManagers\AvgResponsibleProcessingRecordRelationManager;
use App\Filament\RelationManagers\DataBreachRecordRelationManager;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\RelationManagers\WpgProcessingRecordRelationManager;
use App\Filament\Resources\ResponsibleResource\Pages\CreateResponsible;
use App\Filament\Resources\ResponsibleResource\Pages\EditResponsible;
use App\Filament\Resources\ResponsibleResource\Pages\ListResponsibles;
use App\Filament\Resources\ResponsibleResource\Pages\ViewResponsible;
use App\Filament\Resources\ResponsibleResource\ResponsibleResourceForm;
use App\Filament\Resources\ResponsibleResource\ResponsibleResourceInfolist;
use App\Filament\Resources\ResponsibleResource\ResponsibleResourceTable;
use App\Models\Responsible;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

use function __;

class ResponsibleResource extends Resource
{
    protected static ?string $model = Responsible::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::MANAGEMENT->value);
    }

    public static function form(Schema $schema): Schema
    {
        return ResponsibleResourceForm::form($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ResponsibleResourceInfolist::infolist($schema);
    }

    public static function table(Table $table): Table
    {
        return ResponsibleResourceTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            SnapshotsRelationManager::class,
            AvgResponsibleProcessingRecordRelationManager::class,
            AvgProcessorProcessingRecordRelationManager::class,
            WpgProcessingRecordRelationManager::class,
            DataBreachRecordRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResponsibles::route('/'),
            'create' => CreateResponsible::route('/create'),
            'view' => ViewResponsible::route('/{record}'),
            'edit' => EditResponsible::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('responsible.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('responsible.model_plural');
    }
}
