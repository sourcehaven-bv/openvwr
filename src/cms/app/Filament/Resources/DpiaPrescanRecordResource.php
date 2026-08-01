<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RegisterLayout;
use App\Facades\Authentication;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\Resources\DpiaPrescanRecordResource\DpiaPrescanRecordResourceForm;
use App\Filament\Resources\DpiaPrescanRecordResource\DpiaPrescanRecordResourceTable;
use App\Filament\Resources\DpiaPrescanRecordResource\Pages;
use App\Models\Dpia\DpiaPrescanRecord;
use Filament\Forms\Form;
use Filament\Tables\Table;

use function __;

class DpiaPrescanRecordResource extends Resource
{
    protected static ?string $model = DpiaPrescanRecord::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    // Sorted before the DPIA itself: the pre-scan is what you fill in first.
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::DPIA->value);
    }

    public static function form(Form $form): Form
    {
        return match (Authentication::user()->register_layout) {
            RegisterLayout::STEPS => DpiaPrescanRecordResourceForm::stepsForm($form),
            RegisterLayout::ONE_PAGE => DpiaPrescanRecordResourceForm::onePageForm($form),
        };
    }

    public static function table(Table $table): Table
    {
        return DpiaPrescanRecordResourceTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDpiaPrescanRecords::route('/'),
            'create' => Pages\CreateDpiaPrescanRecord::route('/create'),
            'edit' => Pages\EditDpiaPrescanRecord::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('dpia_prescan_record.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('dpia_prescan_record.model_plural');
    }
}
