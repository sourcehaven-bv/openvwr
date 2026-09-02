<?php

declare(strict_types=1);

namespace App\Filament\Resources\WpgProcessingRecordResource;

use App\Filament\Actions\TransferCopyBulkAction;
use App\Filament\Actions\TransferExportBulkAction;
use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\EntityNumber;
use App\Filament\Tables\Columns\ExpiringDateColumn;
use App\Filament\Tables\Columns\ImportNumberColumn;
use App\Filament\Tables\Columns\SnapshotStatusColumn;
use App\Filament\Tables\Columns\TagsColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Filament\Tables\ContactPersonFilter;
use App\Filament\Tables\DateWindowFilter;
use App\Filament\Tables\DocumentFilter;
use App\Filament\Tables\ProcessorFilter;
use App\Filament\Tables\ResponsibleFilter;
use App\Filament\Tables\SystemFilter;
use App\Filament\Tables\TagFilter;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function __;

class WpgProcessingRecordResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                EntityNumber::make(),
                TextColumn::make('name')
                    ->label(__('processing_record.name'))
                    ->searchable()
                    ->sortable(),
                SnapshotStatusColumn::make(),
                ExpiringDateColumn::make('review_at')
                    ->label(__('general.review_at')),
                ImportNumberColumn::make(),
                TagsColumn::make(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort('wpg_processing_records.updated_at', 'desc')
            ->emptyStateHeading(__('wpg_processing_record.table_empty_heading'))
            ->emptyStateDescription(null)
            ->recordActionsColumnLabel(__('general.edit'))
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(static fn (EditAction $action) => $action->getLabel()),
            ])
            ->toolbarActions([
                TransferExportBulkAction::make(),
                TransferCopyBulkAction::make(),
            ])
            ->filters([
                DateWindowFilter::make('review_at')
                    ->label(__('general.review_at')),
                ResponsibleFilter::make(),
                ProcessorFilter::make(),
                SystemFilter::make(),
                TagFilter::make(),
                ContactPersonFilter::make(),
                DocumentFilter::make(),
            ]);
    }
}
