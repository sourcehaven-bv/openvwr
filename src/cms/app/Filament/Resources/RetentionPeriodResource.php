<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RetentionPeriodResource\Pages\ListRetentionPeriods;
use App\Filament\Resources\RetentionPeriodResource\Pages\CreateRetentionPeriod;
use App\Filament\Resources\RetentionPeriodResource\Pages\EditRetentionPeriod;
use App\Filament\Resources\RetentionPeriodResource\Pages\ViewRetentionPeriod;
use App\Filament\Resources\RetentionPeriodResource\Pages;
use App\Models\RetentionPeriod;

use function __;

class RetentionPeriodResource extends LookupListResource
{
    protected static ?string $model = RetentionPeriod::class;
    protected static ?int $navigationSort = 8;

    public static function getPages(): array
    {
        return [
            'index' => ListRetentionPeriods::route('/'),
            'create' => CreateRetentionPeriod::route('/create'),
            'edit' => EditRetentionPeriod::route('/{record}/edit'),
            'view' => ViewRetentionPeriod::route('/{record}'),
        ];
    }

    public static function getEmptyStateHeading(): string
    {
        return __('retention_period.table_empty_heading');
    }

    public static function getModelLabel(): string
    {
        return __('retention_period.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('retention_period.model_plural');
    }
}
