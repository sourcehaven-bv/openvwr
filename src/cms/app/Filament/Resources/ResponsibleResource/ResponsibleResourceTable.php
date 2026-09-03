<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResponsibleResource;

use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\TagsColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Filament\Tables\TagFilter;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function __;

class ResponsibleResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('responsible.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('responsible.phone'))
                    ->sortable(),
                TagsColumn::make(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->filters([
                TagFilter::make(),
            ])
            ->defaultSort('responsibles.updated_at', 'desc')
            ->emptyStateHeading(__('responsible.table_empty_heading'))
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
