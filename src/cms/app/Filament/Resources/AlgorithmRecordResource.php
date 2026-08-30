<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use App\Filament\Resources\AlgorithmRecordResource\Pages\ListAlgorithmRecords;
use App\Filament\Resources\AlgorithmRecordResource\Pages\CreateAlgorithmRecord;
use App\Filament\Resources\AlgorithmRecordResource\Pages\ViewAlgorithmRecord;
use App\Filament\Resources\AlgorithmRecordResource\Pages\EditAlgorithmRecord;
use App\Enums\RegisterLayout;
use App\Facades\Authentication;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\AlgorithmPublicationCategoryRelationManager;
use App\Filament\RelationManagers\AlgorithmStatusRelationManager;
use App\Filament\RelationManagers\AlgorithmThemeRelationManager;
use App\Filament\RelationManagers\AvgProcessorProcessingRecordRelationManager;
use App\Filament\RelationManagers\AvgResponsibleProcessingRecordRelationManager;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\RelationManagers\WpgProcessingRecordRelationManager;
use App\Filament\Resources\AlgorithmRecordResource\AlgorithmRecordResourceForm;
use App\Filament\Resources\AlgorithmRecordResource\AlgorithmRecordResourceInfolist;
use App\Filament\Resources\AlgorithmRecordResource\AlgorithmRecordResourceTable;
use App\Filament\Resources\AlgorithmRecordResource\Pages;
use App\Models\Algorithm\AlgorithmRecord;
use Filament\Tables\Table;

use function __;

class AlgorithmRecordResource extends Resource
{
    protected static bool $hasNavigationBadge = true;
    protected static ?string $model = AlgorithmRecord::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calculator';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::REGISTERS->value);
    }

    public static function form(Schema $schema): Schema
    {
        return match (Authentication::user()->register_layout) {
            RegisterLayout::STEPS => AlgorithmRecordResourceForm::stepsForm($schema),
            RegisterLayout::ONE_PAGE => AlgorithmRecordResourceForm::onePageForm($schema),
        };
    }

    public static function infolist(Schema $schema): Schema
    {
        return match (Authentication::user()->register_layout) {
            RegisterLayout::STEPS => AlgorithmRecordResourceInfolist::stepsInfolist($schema),
            RegisterLayout::ONE_PAGE => AlgorithmRecordResourceInfolist::onePageInfolist($schema),
        };
    }

    public static function table(Table $table): Table
    {
        return AlgorithmRecordResourceTable::table($table);
    }

    public static function getRelations(): array
    {
        return [
            SnapshotsRelationManager::class,
            AlgorithmThemeRelationManager::class,
            AlgorithmStatusRelationManager::class,
            AlgorithmPublicationCategoryRelationManager::class,
            AvgResponsibleProcessingRecordRelationManager::class,
            AvgProcessorProcessingRecordRelationManager::class,
            WpgProcessingRecordRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAlgorithmRecords::route('/'),
            'create' => CreateAlgorithmRecord::route('/create'),
            'view' => ViewAlgorithmRecord::route('/{record}'),
            'edit' => EditAlgorithmRecord::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('algorithm_record.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('algorithm_record.model_plural');
    }
}
