<?php

declare(strict_types=1);

namespace App\Filament\Resources\AlgorithmRecordResource;

use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\EntityNumber;
use App\Filament\Tables\Columns\SnapshotStatusColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Filament\Tables\DocumentFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function __;

class AlgorithmRecordResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                EntityNumber::make()
                    ->label(__('algorithm_record.number')),
                TextColumn::make('name')
                    ->label(__('processing_record.name'))
                    ->searchable()
                    ->sortable(),
                SnapshotStatusColumn::make(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort('algorithm_records.updated_at', 'desc')
            ->emptyStateHeading(__('algorithm_record.table_empty_heading'))
            ->emptyStateDescription(null)
            ->actionsColumnLabel(__('general.edit'))
            ->actions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(static fn (EditAction $action) => $action->getLabel()),
            ])
            ->filters([
                DocumentFilter::make(),
            ]);
    }
}
