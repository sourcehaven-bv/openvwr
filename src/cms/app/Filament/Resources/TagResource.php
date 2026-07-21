<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\AvgProcessorProcessingRecordRelationManager;
use App\Filament\RelationManagers\AvgResponsibleProcessingRecordRelationManager;
use App\Filament\RelationManagers\WpgProcessingRecordRelationManager;
use App\Filament\Resources\TagResource\Pages;
use App\Filament\Resources\TagResource\TagResourceForm;
use App\Filament\Resources\TagResource\TagResourceInfolist;
use App\Filament\Resources\TagResource\TagResourceTable;
use App\Models\Tag;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Tables\Table;

use function __;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 12;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::MANAGEMENT->value);
    }

    public static function form(Form $form): Form
    {
        return TagResourceForm::form($form);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return TagResourceInfolist::infolist($infolist);
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
            'view' => Pages\ViewTag::route('/{record}'),
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
