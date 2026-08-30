<?php

declare(strict_types=1);

namespace App\Livewire\Snapshot;

use Filament\Actions\Concerns\InteractsWithActions;
use App\Filament\Resources\RelatedSnapshotSourceResource;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

use function __;
use function view;

class RelatedSnapshotSourcesValidation extends Component implements HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public Snapshot $snapshot;

    public function table(Table $table): Table
    {
        return RelatedSnapshotSourceResource::table($table)
            ->query(
                RelatedSnapshotSource::where(['snapshot_id' => $this->snapshot->id])
                    ->whereDoesntHave('snapshotSource', static function (Builder $query): Builder {
                        return $query->whereHas('snapshots', static function (Builder $query): Builder {
                            return $query->where(['state' => Established::$name]);
                        });
                    }),
            )
            ->emptyStateIcon('heroicon-o-check')
            ->emptyStateHeading(__('snapshot_transition.establish.related_snapshot_sources_approved'));
    }

    public function render(): View
    {
        return view('livewire.filament.table');
    }
}
