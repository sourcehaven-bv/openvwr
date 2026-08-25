<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord;

use App\Filament\Actions\TransferCopyBulkAction;
use App\Filament\Actions\TransferExportBulkAction;
use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\DataBreachRecordStateColumn;
use App\Filament\Tables\Columns\EntityNumber;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Filament\Tables\DocumentFilter;
use App\Filament\Tables\OpenDataBreachFilter;
use App\Filament\Tables\ResponsibleFilter;
use App\Filament\Tables\TagFilter;
use App\Models\States\DataBreachRecordState;
use App\Services\DateFormatService;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

use function __;
use function collect;
use function sprintf;

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
                DataBreachRecordStateColumn::make(),
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
            ->bulkActions([
                TransferExportBulkAction::make(),
                TransferCopyBulkAction::make(),
            ])
            ->filters([
                TagFilter::make(),
                OpenDataBreachFilter::make(),
                SelectFilter::make('state')
                    ->label(__('data_breach_record.state'))
                    ->multiple()
                    ->options(static function (): array {
                        return collect(DataBreachRecordState::all())
                            ->map(static function ($value, $key): string {
                                return __(sprintf('data_breach_record_state.label.%s', $key));
                            })
                            ->toArray();
                    }),
                TernaryFilter::make('ap_reported')
                    ->label(__('data_breach_record.ap_reported')),
                ResponsibleFilter::make(),
                DocumentFilter::make(),
            ]);
    }
}
