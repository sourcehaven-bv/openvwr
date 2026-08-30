<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\WpgProcessingRecordServiceResource\Pages\ListWpgProcessingRecordServices;
use App\Filament\Resources\WpgProcessingRecordServiceResource\Pages\CreateWpgProcessingRecordService;
use App\Filament\Resources\WpgProcessingRecordServiceResource\Pages\EditWpgProcessingRecordService;
use App\Filament\Resources\WpgProcessingRecordServiceResource\Pages\ViewWpgProcessingRecordService;
use App\Config\Feature;
use App\Filament\RelationManagers\WpgProcessingRecordRelationManager;
use App\Filament\Resources\WpgProcessingRecordServiceResource\Pages;
use App\Models\Wpg\WpgProcessingRecordService;
use Illuminate\Database\Eloquent\Model;

use function __;

class WpgProcessingRecordServiceResource extends LookupListResource
{
    protected static ?string $model = WpgProcessingRecordService::class;
    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return Feature::wpgEnabled() && parent::shouldRegisterNavigation();
    }

    /**
     * Filament checks this before it registers the routes' pages and before it
     * renders a record page, so switching the feature off also makes the urls
     * unreachable instead of only hiding the menu entry.
     */
    public static function canViewAny(): bool
    {
        return Feature::wpgEnabled() && parent::canViewAny();
    }

    public static function canCreate(): bool
    {
        return Feature::wpgEnabled() && parent::canCreate();
    }

    public static function canView(Model $record): bool
    {
        return Feature::wpgEnabled() && parent::canView($record);
    }

    public static function canEdit(Model $record): bool
    {
        return Feature::wpgEnabled() && parent::canEdit($record);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWpgProcessingRecordServices::route('/'),
            'create' => CreateWpgProcessingRecordService::route('/create'),
            'edit' => EditWpgProcessingRecordService::route('/{record}/edit'),
            'view' => ViewWpgProcessingRecordService::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            WpgProcessingRecordRelationManager::class,
        ];
    }

    public static function getEmptyStateHeading(): string
    {
        return __('wpg_processing_record_service.table_empty_heading');
    }

    public static function getModelLabel(): string
    {
        return __('wpg_processing_record_service.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('wpg_processing_record_service.model_plural');
    }
}
