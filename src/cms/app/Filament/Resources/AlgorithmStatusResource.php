<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AlgorithmStatusResource\Pages\ListAlgorithmStatuses;
use App\Filament\Resources\AlgorithmStatusResource\Pages\CreateAlgorithmStatus;
use App\Filament\Resources\AlgorithmStatusResource\Pages\EditAlgorithmStatus;
use App\Filament\Resources\AlgorithmStatusResource\Pages\ViewAlgorithmStatus;
use App\Filament\RelationManagers\AlgorithmRecordRelationManager;
use App\Filament\Resources\AlgorithmStatusResource\Pages;
use App\Models\Algorithm\AlgorithmStatus;

use function __;

class AlgorithmStatusResource extends LookupListResource
{
    protected static ?string $model = AlgorithmStatus::class;
    protected static ?int $navigationSort = 10;

    public static function getPages(): array
    {
        return [
            'index' => ListAlgorithmStatuses::route('/'),
            'create' => CreateAlgorithmStatus::route('/create'),
            'edit' => EditAlgorithmStatus::route('/{record}/edit'),
            'view' => ViewAlgorithmStatus::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            AlgorithmRecordRelationManager::class,
        ];
    }

    public static function getEmptyStateHeading(): string
    {
        return __('algorithm_status.table_empty_heading');
    }

    public static function getModelLabel(): string
    {
        return __('algorithm_status.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('algorithm_status.model_plural');
    }
}
