<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RegisterLayout;
use App\Facades\Authentication;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\AvgResponsibleProcessingRecordResourceForm;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\AvgResponsibleProcessingRecordResourceInfolist;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\AvgResponsibleProcessingRecordResourceTable;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use function __;

class AvgResponsibleProcessingRecordResource extends Resource
{
    protected static bool $hasNavigationBadge = true;
    protected static ?string $model = AvgResponsibleProcessingRecord::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::REGISTERS->value);
    }

    public static function form(Form $form): Form
    {
        return match (Authentication::user()->register_layout) {
            RegisterLayout::STEPS => AvgResponsibleProcessingRecordResourceForm::stepsForm($form),
            RegisterLayout::ONE_PAGE => AvgResponsibleProcessingRecordResourceForm::onePageForm($form),
        };
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return match (Authentication::user()->register_layout) {
            RegisterLayout::STEPS => AvgResponsibleProcessingRecordResourceInfolist::stepsInfolist($infolist),
            RegisterLayout::ONE_PAGE => AvgResponsibleProcessingRecordResourceInfolist::onePageInfolist($infolist),
        };
    }

    public static function table(Table $table): Table
    {
        return AvgResponsibleProcessingRecordResourceTable::table($table);
    }

    /**
     * @return Builder<AvgResponsibleProcessingRecord>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'stakeholders.stakeholderDataItems',
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SnapshotsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAvgResponsibleProcessingRecords::route('/'),
            'create' => Pages\CreateAvgResponsibleProcessingRecord::route('/create'),
            'view' => Pages\ViewAvgResponsibleProcessingRecord::route('/{record}'),
            'edit' => Pages\EditAvgResponsibleProcessingRecord::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('avg_responsible_processing_record.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('avg_responsible_processing_record.model_plural');
    }
}
