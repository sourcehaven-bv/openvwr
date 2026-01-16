<?php

declare(strict_types=1);

namespace App\Livewire\Snapshot;

use App\Collections\SnapshotApprovalCollection;
use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\User;
use App\Services\DateFormatService;
use App\Services\Snapshot\SnapshotApprovalService;
use Filament\Forms\Components\Component as FilamentFormComponent;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Webmozart\Assert\Assert;

use function __;
use function view;

class Approvals extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Snapshot $snapshot;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('snapshot_approval.invited'))
            ->query(SnapshotApproval::where(['snapshot_id' => $this->snapshot->id]))
            ->columns([
                IconColumn::make('status')
                    ->label(__('snapshot_approval.status'))
                    ->color(static function (SnapshotApprovalStatus $state): string {
                        return match ($state) {
                            SnapshotApprovalStatus::APPROVED => 'success',
                            SnapshotApprovalStatus::DECLINED => 'danger',
                            SnapshotApprovalStatus::UNKNOWN => 'gray',
                        };
                    })
                    ->icon(static function (SnapshotApprovalStatus $state): string {
                        return match ($state) {
                            SnapshotApprovalStatus::APPROVED => 'heroicon-o-check-circle',
                            SnapshotApprovalStatus::DECLINED => 'heroicon-o-x-mark',
                            SnapshotApprovalStatus::UNKNOWN => 'heroicon-o-clock',
                        };
                    }),
                TextColumn::make('assignedTo.name')
                    ->label(__('snapshot_approval.assigned_to')),
                TextColumn::make('notified_at')
                    ->label(__('snapshot_approval.last_notified_at'))
                    ->dateTime(DateFormatService::FORMAT_DATE_TIME, DateFormatService::getDisplayTimezone()),
            ])
            ->emptyStateHeading(__('snapshot_approval.table_empty_heading'))
            ->emptyStateDescription(null)
            ->headerActions([
                Action::make('snapshot_approval_request_action')
                    ->label(__('snapshot_approval.request'))
                    ->modalSubmitActionLabel(__('general.add'))
                    ->color('gray')
                    ->form($this->createRequestApprovalForm())
                    ->visible(Authorization::hasPermission(Permission::SNAPSHOT_APPROVAL_CREATE))
                    ->action(function (array $data, SnapshotApprovalService $snapshotApprovalService): void {
                        $requestedBy = Authentication::user();
                        Assert::keyExists($data, 'user_ids');
                        Assert::isArray($data['user_ids']);

                        foreach ($data['user_ids'] as $userId) {
                            $assignedTo = User::where('id', $userId)->firstOrFail();
                            $snapshotApprovalService->create($this->snapshot, $requestedBy, $assignedTo);
                        }
                    })
                    ->after(static function (Component $livewire): void {
                        $livewire->dispatch(ViewSnapshot::REFRESH_LIVEWIRE_COMPONENT);
                    }),
            ])
            ->bulkActions([
                BulkAction::make('snapshot_approval_notify_bulk_delete')
                    ->label(__('general.delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(Authorization::hasPermission(Permission::SNAPSHOT_APPROVAL_DELETE))
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(
                        static function (SnapshotApprovalCollection $records, SnapshotApprovalService $snapshotApprovalService): void {
                            $records->each(static function (SnapshotApproval $snapshotApproval) use ($snapshotApprovalService): void {
                                $snapshotApprovalService->delete($snapshotApproval, Authentication::user());
                            });
                        },
                    )
                    ->after(static function (Component $livewire): void {
                        $livewire->dispatch(ViewSnapshot::REFRESH_LIVEWIRE_COMPONENT);
                    }),
            ])->paginated(false);
    }

    public function render(): View
    {
        return view('livewire.filament.table');
    }

    /**
     * @return array<FilamentFormComponent>
     */
    private function createRequestApprovalForm(): array
    {
        $allowedUserIds = $this->snapshot->organisation->users()
            ->whereHas('organisationRoles', static function (Builder $query): Builder {
                return $query->where(['role' => Role::MANDATE_HOLDER]);
            })
            ->whereNot('user_id', Authentication::user()->id)
            ->whereNotIn('user_id', $this->snapshot->snapshotApprovals()
                ->get(['assigned_to'])
                ->pluck('assigned_to')
                ->toArray())
            ->get()
            ->pluck('name', 'id')
            ->sort()
            ->toArray();
        Assert::isMap($allowedUserIds);
        Assert::allString($allowedUserIds);

        return [
            CheckboxList::makeWithValidatedOptions('user_ids', $allowedUserIds)
                ->label(''),
        ];
    }
}
