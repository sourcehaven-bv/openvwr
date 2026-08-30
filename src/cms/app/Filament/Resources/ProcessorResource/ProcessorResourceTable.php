<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProcessorResource;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\TagsColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Filament\Tables\TagFilter;
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
                TagsColumn::make(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->filters([
                TagFilter::make(),
            ])
            ->defaultSort('processors.updated_at', 'desc')
            ->emptyStateHeading(__('processor.table_empty_heading'))
            ->emptyStateDescription(null)
            ->recordActionsColumnLabel(__('general.edit'))
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(static fn (EditAction $action) => $action->getLabel()),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
