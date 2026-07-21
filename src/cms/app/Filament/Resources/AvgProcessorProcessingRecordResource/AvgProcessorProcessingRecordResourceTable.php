<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgProcessorProcessingRecordResource;

use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\EntityNumber;
use App\Filament\Tables\Columns\ExpiringDateColumn;
use App\Filament\Tables\Columns\ImportNumberColumn;
use App\Filament\Tables\Columns\SnapshotStatusColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Filament\Tables\ContactPersonFilter;
use App\Filament\Tables\DocumentFilter;
use App\Filament\Tables\ProcessorFilter;
use App\Filament\Tables\ReceiverFilter;
use App\Filament\Tables\ResponsibleFilter;
use App\Filament\Tables\SystemFilter;
use App\Filament\Tables\TagFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use function __;

class AvgProcessorProcessingRecordResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                EntityNumber::make(),
                TextColumn::make('name')
                    ->label(__('processing_record.name'))
                    ->sortable()
                    ->searchable(),
                SnapshotStatusColumn::make(),
                ExpiringDateColumn::make('review_at')
                    ->label(__('general.review_at')),
                ImportNumberColumn::make(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort('avg_processor_processing_records.updated_at', 'desc')
            ->emptyStateHeading(__('avg_processor_processing_record.table_empty_heading'))
            ->emptyStateDescription(null)
            ->actionsColumnLabel(__('general.edit'))
            ->actions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(static fn (EditAction $action) => $action->getLabel()),
            ])
            ->filters([
                ResponsibleFilter::make(),
                ProcessorFilter::make(),
                ReceiverFilter::make(),
                TagFilter::make(),
                SystemFilter::make(),
                ContactPersonFilter::make(),
                DocumentFilter::make(),
            ]);
    }
}
