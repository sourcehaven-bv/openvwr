<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\AvgProcessorProcessingRecordResource\Pages\ListAvgProcessorProcessingRecords;
use App\Filament\Resources\AvgProcessorProcessingRecordResource\Pages\CreateAvgProcessorProcessingRecord;
use App\Filament\Resources\AvgProcessorProcessingRecordResource\Pages\ViewAvgProcessorProcessingRecord;
use App\Filament\Resources\AvgProcessorProcessingRecordResource\Pages\EditAvgProcessorProcessingRecord;
use App\Enums\RegisterLayout;
use App\Facades\Authentication;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\Resources\AvgProcessorProcessingRecordResource\AvgProcessorProcessingRecordResourceForm;
use App\Filament\Resources\AvgProcessorProcessingRecordResource\AvgProcessorProcessingRecordResourceInfolist;
use App\Filament\Resources\AvgProcessorProcessingRecordResource\AvgProcessorProcessingRecordResourceTable;
use App\Filament\Resources\AvgProcessorProcessingRecordResource\Pages;
use App\Models\Avg\AvgProcessorProcessingRecord;
use Filament\Tables\Table;

use function __;

class AvgProcessorProcessingRecordResource extends Resource
{
    protected static bool $hasNavigationBadge = true;
    protected static ?string $model = AvgProcessorProcessingRecord::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::REGISTERS->value);
    }

    public static function form(Schema $schema): Schema
    {
        return match (Authentication::user()->register_layout) {
            RegisterLayout::STEPS => AvgProcessorProcessingRecordResourceForm::stepsForm($schema),
            RegisterLayout::ONE_PAGE => AvgProcessorProcessingRecordResourceForm::onePageForm($schema),
        };
    }

    public static function infolist(Schema $schema): Schema
    {
        return match (Authentication::user()->register_layout) {
            RegisterLayout::STEPS => AvgProcessorProcessingRecordResourceInfolist::stepsInfolist($schema),
            RegisterLayout::ONE_PAGE => AvgProcessorProcessingRecordResourceInfolist::onePageInfolist($schema),
        };
    }

    public static function table(Table $table): Table
    {
        return AvgProcessorProcessingRecordResourceTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAvgProcessorProcessingRecords::route('/'),
            'create' => CreateAvgProcessorProcessingRecord::route('/create'),
            'view' => ViewAvgProcessorProcessingRecord::route('/{record}'),
            'edit' => EditAvgProcessorProcessingRecord::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            SnapshotsRelationManager::class,
        ];
    }

    public static function getModelLabel(): string
    {
        return __('avg_processor_processing_record.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('avg_processor_processing_record.model_plural');
    }
}
