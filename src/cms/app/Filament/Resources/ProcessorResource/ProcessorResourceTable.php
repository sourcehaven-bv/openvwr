<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProcessorResource;

use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function __;

class ProcessorResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('processor.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('processor.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('processor.phone'))
                    ->sortable(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort('processors.updated_at', 'desc')
            ->emptyStateHeading(__('processor.table_empty_heading'))
            ->emptyStateDescription(null)
            ->actionsColumnLabel(__('general.edit'))
            ->actions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(static fn (EditAction $action) => $action->getLabel()),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
