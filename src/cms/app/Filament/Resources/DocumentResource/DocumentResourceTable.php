<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentResource;

use App\Facades\Authentication;
use App\Filament\Resources\DocumentResource;
use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\ExpiringDateColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Models\Document;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use function __;

class DocumentResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('document.name'))
                    ->searchable()
                    ->sortable(),
                ExpiringDateColumn::make('expires_at')
                    ->label(__('document.expires_at')),
                ExpiringDateColumn::make('notify_at')
                    ->label(__('document.notify_at')),
                TextColumn::make('documentType.name')
                    ->label(__('document.type'))
                    ->sortable(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->relationship('documentType', 'name', static function (Builder $query): void {
                        $query->whereBelongsTo(Authentication::organisation());
                    })
                    ->preload()
                    ->multiple(),
            ])
            ->defaultSort('documents.updated_at', 'desc')
            ->recordUrl(static function (Document $record): string {
                return DocumentResource::getUrl('view', ['record' => $record]);
            })
            ->emptyStateHeading(__('document.table_empty_heading'))
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
