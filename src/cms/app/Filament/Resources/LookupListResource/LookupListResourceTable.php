<?php

declare(strict_types=1);

namespace App\Filament\Resources\LookupListResource;

use App\Filament\Actions\DeleteBulkActionWithRelationChecks;
use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

use function __;
use function sprintf;

class LookupListResourceTable
{
    /**
     * @param class-string<Model> $model
     */
    public static function table(Table $table, string $emptyStateHeading, string $model): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('general.name'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('enabled')
                    ->label(__('general.enabled'))
                    ->boolean(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort(sprintf('%s.updated_at', $model::getModel()->getTable()), 'desc')
            ->emptyStateHeading($emptyStateHeading)
            ->emptyStateDescription(null)
            ->actionsColumnLabel(__('general.edit'))
            ->actions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(static fn (EditAction $action) => $action->getLabel()),
            ])
            ->bulkActions([
                DeleteBulkActionWithRelationChecks::make(),
            ]);
    }
}
