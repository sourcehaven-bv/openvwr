<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RegisterLayout;
use App\Facades\Authentication;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\AlgorithmPublicationCategoryRelationManager;
use App\Filament\RelationManagers\AlgorithmStatusRelationManager;
use App\Filament\RelationManagers\AlgorithmThemeRelationManager;
use App\Filament\RelationManagers\DocumentRelationManager;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\Resources\AlgorithmRecordResource\AlgorithmRecordResourceForm;
use App\Filament\Resources\AlgorithmRecordResource\AlgorithmRecordResourceInfolist;
use App\Filament\Resources\AlgorithmRecordResource\AlgorithmRecordResourceTable;
use App\Filament\Resources\AlgorithmRecordResource\Pages;
use App\Models\Algorithm\AlgorithmRecord;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Tables\Table;

use function __;

class AlgorithmRecordResource extends Resource
{
    protected static bool $hasNavigationBadge = true;
    protected static ?string $model = AlgorithmRecord::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::REGISTERS->value);
    }

    public static function form(Form $form): Form
    {
        return match (Authentication::user()->register_layout) {
            RegisterLayout::STEPS => AlgorithmRecordResourceForm::stepsForm($form),
            RegisterLayout::ONE_PAGE => AlgorithmRecordResourceForm::onePageForm($form),
        };
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return match (Authentication::user()->register_layout) {
            RegisterLayout::STEPS => AlgorithmRecordResourceInfolist::stepsInfolist($infolist),
            RegisterLayout::ONE_PAGE => AlgorithmRecordResourceInfolist::onePageInfolist($infolist),
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
            DocumentRelationManager::make(),
            AlgorithmThemeRelationManager::class,
            AlgorithmStatusRelationManager::class,
            AlgorithmPublicationCategoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlgorithmRecords::route('/'),
            'create' => Pages\CreateAlgorithmRecord::route('/create'),
            'view' => Pages\ViewAlgorithmRecord::route('/{record}'),
            'edit' => Pages\EditAlgorithmRecord::route('/{record}/edit'),
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
