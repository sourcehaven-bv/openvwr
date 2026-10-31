<?php

declare(strict_types=1);

namespace App\Filament\Resources\SnapshotResource;

use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\SnapshotStateColumn;
use App\Models\Builders\SnapshotBuilder;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use App\Services\DateFormatService;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function __;
use function route;

class SnapshotResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version')
                    ->label(__('snapshot.version')),
                CreatedAtColumn::make()
                    ->toggleable(false),
                SnapshotStateColumn::make(),
                TextColumn::make('established_at')
                    ->label(__('snapshot.established_at'))
                    ->dateTime(DateFormatService::FORMAT_DATE_TIME, DateFormatService::getDisplayTimezone())
                    ->sortable(),
                TextColumn::make('replaced_at')
                    ->label(__('snapshot.replaced_at'))
                    ->dateTime(DateFormatService::FORMAT_DATE_TIME, DateFormatService::getDisplayTimezone()),
            ])
            ->modifyQueryUsing(static function (SnapshotBuilder $query): void {
                $query->orderByVersion();
            })
            ->emptyStateHeading(__('snapshot.table_empty_heading'))
            ->emptyStateDescription(null)
            ->recordActions([
                // A concept has no fixed content to view: it mirrors the record's form,
                // which is the better place to look at it and the only place to change it.
                ViewAction::make()
                    ->hidden(static function (Snapshot $snapshot): bool {
                        return $snapshot->state instanceof Concept;
                    })
                    ->url(static function (Snapshot $snapshot): string {
                        return route(ViewSnapshot::getRouteName(), [
                            'tenant' => Filament::getTenant(),
                            'record' => $snapshot,
                        ]);
                    }),
            ]);
    }
}
