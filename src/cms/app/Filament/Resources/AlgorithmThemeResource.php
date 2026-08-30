<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AlgorithmThemeResource\Pages\ListAlgorithmThemes;
use App\Filament\Resources\AlgorithmThemeResource\Pages\CreateAlgorithmTheme;
use App\Filament\Resources\AlgorithmThemeResource\Pages\EditAlgorithmTheme;
use App\Filament\Resources\AlgorithmThemeResource\Pages\ViewAlgorithmTheme;
use App\Filament\RelationManagers\AlgorithmRecordRelationManager;
use App\Filament\Resources\AlgorithmThemeResource\Pages;
use App\Models\Algorithm\AlgorithmTheme;

use function __;

class AlgorithmThemeResource extends LookupListResource
{
    protected static ?string $model = AlgorithmTheme::class;
    protected static ?int $navigationSort = 11;

    public static function getPages(): array
    {
        return [
            'index' => ListAlgorithmThemes::route('/'),
            'create' => CreateAlgorithmTheme::route('/create'),
            'edit' => EditAlgorithmTheme::route('/{record}/edit'),
            'view' => ViewAlgorithmTheme::route('/{record}'),
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
        return __('algorithm_theme.table_empty_heading');
    }

    public static function getModelLabel(): string
    {
        return __('algorithm_theme.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('algorithm_theme.model_plural');
    }
}
