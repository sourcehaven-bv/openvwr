<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RegisterLayout;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\Resources\DpiaPrescanRecordResource\DpiaPrescanRecordResourceForm;
use App\Filament\Resources\DpiaPrescanRecordResource\DpiaPrescanRecordResourceTable;
use App\Filament\Resources\DpiaPrescanRecordResource\Pages\CreateDpiaPrescanRecord;
use App\Filament\Resources\DpiaPrescanRecordResource\Pages\EditDpiaPrescanRecord;
use App\Filament\Resources\DpiaPrescanRecordResource\Pages\ListDpiaPrescanRecords;
use App\Models\Dpia\DpiaPrescanRecord;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

use function __;

class DpiaPrescanRecordResource extends Resource
{
    protected static ?string $model = DpiaPrescanRecord::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    // Sorted before the DPIA itself: the pre-scan is what you fill in first.
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::DPIA->value);
    }

    public static function form(Schema $schema): Schema
    {
        return match (RegisterLayout::forActingUser()) {
            RegisterLayout::STEPS => DpiaPrescanRecordResourceForm::stepsForm($schema),
            RegisterLayout::ONE_PAGE => DpiaPrescanRecordResourceForm::onePageForm($schema),
        };
    }

    public static function table(Table $table): Table
    {
        return DpiaPrescanRecordResourceTable::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDpiaPrescanRecords::route('/'),
            'create' => CreateDpiaPrescanRecord::route('/create'),
            'edit' => EditDpiaPrescanRecord::route('/{record}/edit'),
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
