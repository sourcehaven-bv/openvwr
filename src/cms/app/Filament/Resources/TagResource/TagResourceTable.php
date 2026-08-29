<?php

declare(strict_types=1);

namespace App\Filament\Resources\TagResource;

use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Models\Tag;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

use function __;

class TagResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static function (Builder $query): void {
                $query->addSelect([
                    'items_count' => DB::table('taggables')
                        ->selectRaw('count(*)')
                        ->whereColumn('taggables.tag_id', 'tags.id'),
                ]);
            })
            ->columns([
                TextColumn::make('name')
                    ->label(__('general.name'))
                    ->badge()
                    ->color(static fn (Tag $tag): string => $tag->color->value ?? 'gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label(__('tag.items_count'))
                    ->numeric()
                    ->sortable(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort('tags.updated_at', 'desc')
            ->emptyStateHeading(__('tag.table_empty_heading'))
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
