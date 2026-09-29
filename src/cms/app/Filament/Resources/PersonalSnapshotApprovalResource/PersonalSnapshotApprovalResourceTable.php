<?php

declare(strict_types=1);

namespace App\Filament\Resources\PersonalSnapshotApprovalResource;

use App\Collections\SnapshotCollection;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Facades\Authentication;
use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\SnapshotSourceTypeColumn;
use App\Filament\Tables\Columns\SnapshotStateColumn;
use App\Models\Scopes\OrderByCreatedAtAscScope;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\States\SnapshotState;
use App\Services\DateFormatService;
use App\Services\Snapshot\SnapshotApprovalService;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

use function __;
use function class_basename;
use function collect;
use function sprintf;

class PersonalSnapshotApprovalResourceTable
{
    public const string REFRESH_TABLE_EVENT = 'refresh-personal-snapshot-approvals-table-event';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SnapshotSourceTypeColumn::make(),
                TextColumn::make('name')
                    ->label(__('snapshot.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('version')
                    ->label(__('snapshot.version'))
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                SnapshotStateColumn::make(),
                TextColumn::make('snapshotApprovals')
                    ->label(__('snapshot_approval.status'))
                    ->alignCenter()
                    ->formatStateUsing(static function (Snapshot $record): string {
                        /** @var SnapshotApproval $snapshotApproval */
                        $snapshotApproval = $record->snapshotApprovals()
                            ->where('assigned_to', Authentication::user()->id)
                            ->firstOrFail();

                        return __(sprintf('snapshot_approval_status.%s', $snapshotApproval->status->value));
                    }),
                TextColumn::make('established_at')
                    ->label(__('snapshot.established_at'))
                    ->dateTime(DateFormatService::FORMAT_DATE_TIME, DateFormatService::getDisplayTimezone())
                    ->sortable(),
                CreatedAtColumn::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('snapshot.table_empty_heading'))
            ->emptyStateDescription(null)
            ->filters([
                SelectFilter::make('snapshot_source_type')
                    ->label(__('snapshot.snapshot_source_type'))
                    ->multiple()
                    ->options(static function (): array {
                        $query = Snapshot::tenantQuery()
                            ->withoutGlobalScope(OrderByCreatedAtAscScope::class);
                        $query->getQuery()->distinct('snapshot_source_type');

                        return $query
                            ->get()
                            ->keyBy('snapshot_source_type')
                            ->map(static function (Snapshot $snapshot): string {
                                return __(sprintf('%s.model_singular', Str::snake(class_basename($snapshot->snapshot_source_type))));
                            })->toArray();
                    }),
                SelectFilter::make('state')
                    ->label(__('snapshot.state'))
                    ->multiple()
                    ->options(static function (): array {
                        return collect(SnapshotState::all())
                            ->map(static function ($value, $key): string {
                                return __(sprintf('snapshot_state.label.%s', $key));
                            })
                            ->toArray();
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('snapshot_approval_approve')
                    ->label(__('snapshot_approval.approve'))
                    ->color('success')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(static function (SnapshotCollection $records, SnapshotApprovalService $snapshotApprovalService): void {
                        $records->each(static function (Snapshot $snapshot) use ($snapshotApprovalService): void {
                            /** @var SnapshotApproval $snapshotApproval */
                            $snapshotApproval = $snapshot->snapshotApprovals()
                                ->firstOrCreate([
                                    'assigned_to' => Authentication::user()->id,
                                ]);
                            $snapshotApprovalService->setStatus(
                                Authentication::user(),
                                $snapshotApproval,
                                SnapshotApprovalStatus::APPROVED,
                            );
                        });
                    }),
            ])
            ->checkIfRecordIsSelectableUsing(static function (Snapshot $record): bool {
                return $record->snapshotApprovals()
                    ->where('assigned_to', Authentication::user()->id)
                    ->whereNot('status', SnapshotApprovalStatus::signed())
                    ->exists();
            });
    }
}
