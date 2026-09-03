<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RegisterLayout;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\AvgResponsibleProcessingRecordResourceForm;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\AvgResponsibleProcessingRecordResourceInfolist;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\AvgResponsibleProcessingRecordResourceTable;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\CreateAvgResponsibleProcessingRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\ListAvgResponsibleProcessingRecords;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\ViewAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use function __;

class AvgResponsibleProcessingRecordResource extends Resource
{
    protected static bool $hasNavigationBadge = true;
    protected static ?string $model = AvgResponsibleProcessingRecord::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::REGISTERS->value);
    }

    public static function form(Schema $schema): Schema
    {
        return match (RegisterLayout::forActingUser()) {
            RegisterLayout::STEPS => AvgResponsibleProcessingRecordResourceForm::stepsForm($schema),
            RegisterLayout::ONE_PAGE => AvgResponsibleProcessingRecordResourceForm::onePageForm($schema),
        };
    }

    public static function infolist(Schema $schema): Schema
    {
        return match (RegisterLayout::forActingUser()) {
            RegisterLayout::STEPS => AvgResponsibleProcessingRecordResourceInfolist::stepsInfolist($schema),
            RegisterLayout::ONE_PAGE => AvgResponsibleProcessingRecordResourceInfolist::onePageInfolist($schema),
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
            'index' => ListAvgResponsibleProcessingRecords::route('/'),
            'create' => CreateAvgResponsibleProcessingRecord::route('/create'),
            'view' => ViewAvgResponsibleProcessingRecord::route('/{record}'),
            'edit' => EditAvgResponsibleProcessingRecord::route('/{record}/edit'),
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
