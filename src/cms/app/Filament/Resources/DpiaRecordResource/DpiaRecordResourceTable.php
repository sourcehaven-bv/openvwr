<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaRecordResource;

use App\Enums\Dpia\DpiaSubjectType;
use App\Enums\Dpia\RiskLevel;
use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\EntityNumber;
use App\Filament\Tables\Columns\TagsColumn;
use App\Filament\Tables\Columns\UpdatedAtColumn;
use App\Models\Dpia\DpiaRecord;
use App\Services\DateFormatService;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use function __;
use function now;

class DpiaRecordResourceTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                EntityNumber::make(),
                TextColumn::make('name')
                    ->label(__('dpia_record.name'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('subject_type')
                    ->label(__('dpia_record.subject_type'))
                    ->formatStateUsing(static function (DpiaSubjectType $state): string {
                        return $state->label();
                    }),
                TextColumn::make('risks_count')
                    ->label(__('dpia_record.risk_count'))
                    ->counts('risks'),
                // The number that matters most when scanning the register: what
                // is the worst risk still standing after the measures?
                TextColumn::make('highest_residual_risk')
                    ->label(__('dpia_record.highest_residual_risk'))
                    ->state(static function (DpiaRecord $record): string {
                        $level = $record->highestResidualRiskLevel();

                        return $level instanceof RiskLevel
                            ? $level->label()
                            : __('dpia_record.no_risks');
                    })
                    ->badge()
                    ->color(static function (DpiaRecord $record): string {
                        $level = $record->highestResidualRiskLevel();

                        return $level instanceof RiskLevel
                            ? $level->color()->value
                            : 'gray';
                    }),
                TextColumn::make('assessed_at')
                    ->label(__('dpia_record.assessed_at'))
                    ->date(DateFormatService::FORMAT_DATE, DateFormatService::getDisplayTimezone())
                    ->sortable(),
                TextColumn::make('review_at')
                    ->label(__('dpia_record.review_at'))
                    ->date(DateFormatService::FORMAT_DATE, DateFormatService::getDisplayTimezone())
                    ->sortable(),
                TagsColumn::make(),
                CreatedAtColumn::make(),
                UpdatedAtColumn::make(),
            ])
            ->defaultSort('dpia_records.updated_at', 'desc')
            ->emptyStateHeading(__('dpia_record.table_empty_heading'))
            ->emptyStateDescription(null)
            ->recordActionsColumnLabel(__('general.edit'))
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(static fn (EditAction $action) => $action->getLabel()),
            ])
            ->filters([
                SelectFilter::make('subject_type')
                    ->label(__('dpia_record.subject_type'))
                    ->options(DpiaSubjectType::options()),
                // A DPIA has to be revisited at least every three years, so
                // being able to list the overdue ones is the point of storing
                // the review date at all.
                Filter::make('review_due')
                    ->label(__('dpia_record.review_due'))
                    ->query(static function (Builder $query): Builder {
                        return $query->whereNotNull('review_at')
                            ->whereDate('review_at', '<=', now());
                    })
                    ->toggle(),
            ]);
    }
}
