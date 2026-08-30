<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\TagResource\Pages\ListTags;
use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Filament\Resources\TagResource\Pages\EditTag;
use App\Filament\Resources\TagResource\Pages\ViewTag;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\AlgorithmRecordRelationManager;
use App\Filament\RelationManagers\AvgProcessorProcessingRecordRelationManager;
use App\Filament\RelationManagers\AvgResponsibleProcessingRecordRelationManager;
use App\Filament\RelationManagers\ContactPersonRelationManager;
use App\Filament\RelationManagers\DataBreachRecordRelationManager;
use App\Filament\RelationManagers\DocumentRelationManager;
use App\Filament\RelationManagers\ResponsibleRelationManager;
use App\Filament\RelationManagers\WpgProcessingRecordRelationManager;
use App\Filament\Resources\TagResource\Pages;
use App\Filament\Resources\TagResource\TagResourceForm;
use App\Filament\Resources\TagResource\TagResourceInfolist;
use App\Filament\Resources\TagResource\TagResourceTable;
use App\Models\Tag;
use Filament\Tables\Table;

use function __;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 12;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::MANAGEMENT->value);
    }

    public static function form(Schema $schema): Schema
    {
        return TagResourceForm::form($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TagResourceInfolist::infolist($schema);
    }

    public static function table(Table $table): Table
    {
        return TagResourceTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            AvgResponsibleProcessingRecordRelationManager::class,
            AvgProcessorProcessingRecordRelationManager::class,
            WpgProcessingRecordRelationManager::class,
            AlgorithmRecordRelationManager::class,
            DataBreachRecordRelationManager::class,
            ResponsibleRelationManager::class,
            ContactPersonRelationManager::class,
            DocumentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
            'view' => ViewTag::route('/{record}'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('tag.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('tag.model_plural');
    }
}
