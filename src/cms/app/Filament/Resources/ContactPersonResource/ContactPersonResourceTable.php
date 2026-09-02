<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactPersonResource;

use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\TagsColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Filament\Tables\TagFilter;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function __;

class ContactPersonResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('general.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contactPersonPosition.name')
                    ->label(__('contact_person_position.model_singular'))
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('contact_person.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('contact_person.phone'))
                    ->sortable(),
                TagsColumn::make(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->filters([
                TagFilter::make(),
            ])
            ->defaultSort('contact_persons.updated_at', 'desc')
            ->emptyStateHeading(__('contact_person.table_empty_heading'))
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
