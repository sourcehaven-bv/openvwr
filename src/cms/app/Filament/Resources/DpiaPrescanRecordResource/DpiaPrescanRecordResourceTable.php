<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaPrescanRecordResource;

use Filament\Actions\EditAction;
use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\EntityNumber;
use App\Filament\Tables\Columns\TagsColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Services\DateFormatService;
use App\Services\Dpia\PrescanEvaluator;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function __;
use function app;

class DpiaPrescanRecordResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                EntityNumber::make(),
                TextColumn::make('name')
                    ->label(__('dpia_prescan_record.name'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                // Derived rather than read from the column, so the list stays
                // correct even when a record was saved before a rule changed.
                TextColumn::make('outcome')
                    ->label(__('dpia_prescan_record.assessment_dpia'))
                    ->state(static function (DpiaPrescanRecord $record): string {
                        return app(PrescanEvaluator::class)->dpiaOutcome($record)->label();
                    })
                    ->badge()
                    ->color(static function (DpiaPrescanRecord $record): string {
                        return app(PrescanEvaluator::class)->dpiaOutcome($record)->color()->value;
                    }),
                TextColumn::make('dpia_records_count')
                    ->label(__('dpia_prescan_record.dpia_records'))
                    ->counts('dpiaRecords'),
                TextColumn::make('assessed_at')
                    ->label(__('dpia_prescan_record.assessed_at'))
                    ->date(DateFormatService::FORMAT_DATE, DateFormatService::getDisplayTimezone())
                    ->sortable(),
                TagsColumn::make(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort('dpia_prescan_records.updated_at', 'desc')
            ->emptyStateHeading(__('dpia_prescan_record.table_empty_heading'))
            ->emptyStateDescription(null)
            ->recordActionsColumnLabel(__('general.edit'))
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(static fn (EditAction $action) => $action->getLabel()),
            ]);
    }
}
