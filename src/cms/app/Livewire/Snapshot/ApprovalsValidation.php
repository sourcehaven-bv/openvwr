<?php

declare(strict_types=1);

namespace App\Livewire\Snapshot;

use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

use function __;
use function view;

class ApprovalsValidation extends Component implements HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public Snapshot $snapshot;

    public function table(Table $table): Table
    {
        return $table
            ->query(SnapshotApproval::where('snapshot_id', $this->snapshot->id))
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
                    ->label(__('snapshot_approval.mandateholder')),
            ])
            ->emptyStateHeading(__('snapshot_approval.table_empty_heading'))
            ->emptyStateDescription(null)
            ->paginated(false);
    }

    public function render(): View
    {
        return view('livewire.filament.table');
    }
}
