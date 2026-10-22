<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResponsibleLegalEntityResource;

use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function __;

class ResponsibleLegalEntityResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('responsible_legal_entity.name'))
                    ->searchable()
                    ->sortable(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort('responsible_legal_entity.updated_at', 'desc')
            ->recordActionsColumnLabel(__('general.edit'))
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(static fn (EditAction $action) => $action->getLabel()),
            ])
            ->emptyStateHeading(__('responsible_legal_entity.table_empty_heading'))
            ->emptyStateDescription(null);
    }
}
