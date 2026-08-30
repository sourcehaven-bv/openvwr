<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\ProcessorResource\Pages\ListProcessors;
use App\Filament\Resources\ProcessorResource\Pages\CreateProcessor;
use App\Filament\Resources\ProcessorResource\Pages\ViewProcessor;
use App\Filament\Resources\ProcessorResource\Pages\EditProcessor;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\AvgProcessorProcessingRecordRelationManager;
use App\Filament\RelationManagers\AvgResponsibleProcessingRecordRelationManager;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\RelationManagers\WpgProcessingRecordRelationManager;
use App\Filament\Resources\ProcessorResource\Pages;
use App\Filament\Resources\ProcessorResource\ProcessorResourceForm;
use App\Filament\Resources\ProcessorResource\ProcessorResourceInfolist;
use App\Filament\Resources\ProcessorResource\ProcessorResourceTable;
use App\Models\Processor;
use Filament\Tables\Table;

use function __;

class ProcessorResource extends Resource
{
    protected static ?string $model = Processor::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::MANAGEMENT->value);
    }

    public static function form(Schema $schema): Schema
    {
        return ProcessorResourceForm::form($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProcessorResourceInfolist::infolist($schema);
    }

    public static function table(Table $table): Table
    {
        return ProcessorResourceTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            SnapshotsRelationManager::class,
            AvgResponsibleProcessingRecordRelationManager::class,
            AvgProcessorProcessingRecordRelationManager::class,
            WpgProcessingRecordRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProcessors::route('/'),
            'create' => CreateProcessor::route('/create'),
            'view' => ViewProcessor::route('/{record}'),
            'edit' => EditProcessor::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('processor.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('processor.model_plural');
    }
}
