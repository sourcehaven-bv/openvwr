<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord;

use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\EntityNumber;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Filament\Tables\DocumentFilter;
use App\Filament\Tables\ResponsibleFilter;
use App\Services\DateFormatService;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

use function __;

class DataBreachRecordResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                EntityNumber::make()
                    ->label(__('data_breach_record.number')),
                TextColumn::make('name')
                    ->label(__('data_breach_record.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reported_at')
                    ->label(__('data_breach_record.reported_at'))
                    ->date(DateFormatService::FORMAT_DATE, DateFormatService::getDisplayTimezone())
                    ->sortable(),
                IconColumn::make('ap_reported')
                    ->label(__('data_breach_record.ap_reported'))
                    ->boolean(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort('data_breach_records.updated_at', 'desc')
            ->emptyStateHeading(__('data_breach_record.table_empty_heading'))
            ->emptyStateDescription(null)
            ->actionsColumnLabel(__('general.edit'))
            ->actions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(static fn (EditAction $action) => $action->getLabel()),
            ])
            ->filters([
                TernaryFilter::make('ap_reported')
                    ->label(__('data_breach_record.ap_reported')),
                ResponsibleFilter::make(),
                DocumentFilter::make(),
            ]);
    }
}
