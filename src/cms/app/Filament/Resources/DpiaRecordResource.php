<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RegisterLayout;
use App\Facades\Authentication;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\Resources\DpiaRecordResource\DpiaRecordResourceForm;
use App\Filament\Resources\DpiaRecordResource\DpiaRecordResourceTable;
use App\Filament\Resources\DpiaRecordResource\Pages;
use App\Models\Dpia\DpiaRecord;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use function __;

class DpiaRecordResource extends Resource
{
    protected static ?string $model = DpiaRecord::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::DPIA->value);
    }

    public static function form(Form $form): Form
    {
        return match (Authentication::user()->register_layout) {
            RegisterLayout::STEPS => DpiaRecordResourceForm::stepsForm($form),
            RegisterLayout::ONE_PAGE => DpiaRecordResourceForm::onePageForm($form),
        };
    }

    public static function table(Table $table): Table
    {
        return DpiaRecordResourceTable::table($table);
    }

    /**
     * @return Builder<DpiaRecord>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['risks', 'measures']);
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
            'index' => Pages\ListDpiaRecords::route('/'),
            'create' => Pages\CreateDpiaRecord::route('/create'),
            'edit' => Pages\EditDpiaRecord::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('dpia_record.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dpia_record.model_plural');
    }
}
