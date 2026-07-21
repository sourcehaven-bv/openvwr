<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationResource;

use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function __;

class OrganisationResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('general.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('organisation.slug'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('responsibleLegalEntity.name')
                    ->label(__('responsible_legal_entity.model_singular')),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort('organisations.updated_at', 'desc')
            ->emptyStateHeading(__('organisation.table_empty_heading'))
            ->emptyStateDescription(null)
            ->actionsColumnLabel(__('general.edit'))
            ->actions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(static fn (EditAction $action) => $action->getLabel()),
            ]);
    }
}
