<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\AlgorithmRecordRelationManager;
use App\Filament\RelationManagers\AvgProcessorProcessingRecordRelationManager;
use App\Filament\RelationManagers\AvgResponsibleProcessingRecordRelationManager;
use App\Filament\RelationManagers\DataBreachRecordRelationManager;
use App\Filament\RelationManagers\WpgProcessingRecordRelationManager;
use App\Filament\Resources\DocumentResource\DocumentResourceForm;
use App\Filament\Resources\DocumentResource\DocumentResourceInfolist;
use App\Filament\Resources\DocumentResource\DocumentResourceTable;
use App\Filament\Resources\DocumentResource\Pages\CreateDocument;
use App\Filament\Resources\DocumentResource\Pages\EditDocument;
use App\Filament\Resources\DocumentResource\Pages\ListDocuments;
use App\Filament\Resources\DocumentResource\Pages\ViewDocument;
use App\Models\Document;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

use function __;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?int $navigationSort = 13;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::MANAGEMENT->value);
    }

    public static function form(Schema $schema): Schema
    {
        return DocumentResourceForm::form($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentResourceInfolist::infolist($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentResourceTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'view' => ViewDocument::route('/{record}'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            AvgResponsibleProcessingRecordRelationManager::make(),
            AvgProcessorProcessingRecordRelationManager::make(),
            WpgProcessingRecordRelationManager::make(),
            AlgorithmRecordRelationManager::make(),
            DataBreachRecordRelationManager::make(),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('document.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('document.model_plural');
    }
}
