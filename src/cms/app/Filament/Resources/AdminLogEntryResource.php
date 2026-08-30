<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\Resources\AdminLogEntryResource\AdminLogEntryResourceTable;
use App\Filament\Resources\AdminLogEntryResource\Pages\ListAdminLogEntries;
use App\Models\AdminLogEntry;
use Filament\Resources\Resource;
use Filament\Tables\Table;

use function __;

class AdminLogEntryResource extends Resource
{
    protected static ?string $model = AdminLogEntry::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-command-line';
    protected static ?int $navigationSort = 4;
    protected static bool $isScopedToTenant = false;

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::FUNCTIONAL_MANAGEMENT->value);
    }

    public static function table(Table $table): Table
    {
        return AdminLogEntryResourceTable::make($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminLogEntries::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin_log_entry.model_singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin_log_entry.model_plural');
    }
}
