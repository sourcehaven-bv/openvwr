<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AvgResponsibleProcessingRecordServiceResource\Pages\ListAvgResponsibleProcessingRecordServices;
use App\Filament\Resources\AvgResponsibleProcessingRecordServiceResource\Pages\CreateAvgResponsibleProcessingRecordService;
use App\Filament\Resources\AvgResponsibleProcessingRecordServiceResource\Pages\EditAvgResponsibleProcessingRecordService;
use App\Filament\Resources\AvgResponsibleProcessingRecordServiceResource\Pages\ViewAvgResponsibleProcessingRecordService;
use App\Filament\RelationManagers\AvgResponsibleProcessingRecordRelationManager;
use App\Filament\Resources\AvgResponsibleProcessingRecordServiceResource\Pages;
use App\Models\Avg\AvgResponsibleProcessingRecordService;

use function __;

class AvgResponsibleProcessingRecordServiceResource extends LookupListResource
{
    protected static ?string $model = AvgResponsibleProcessingRecordService::class;
    protected static ?int $navigationSort = 3;

    public static function getPages(): array
    {
        return [
            'index' => ListAvgResponsibleProcessingRecordServices::route('/'),
            'create' => CreateAvgResponsibleProcessingRecordService::route('/create'),
            'edit' => EditAvgResponsibleProcessingRecordService::route('/{record}/edit'),
            'view' => ViewAvgResponsibleProcessingRecordService::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            AvgResponsibleProcessingRecordRelationManager::class,
        ];
    }

    public static function getEmptyStateHeading(): string
    {
        return __('avg_responsible_processing_record_service.table_empty_heading');
    }

    public static function getModelLabel(): string
    {
        return __('avg_responsible_processing_record_service.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('avg_responsible_processing_record_service.model_plural');
    }
}
