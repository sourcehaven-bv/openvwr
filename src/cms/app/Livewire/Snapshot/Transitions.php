<?php

declare(strict_types=1);

namespace App\Livewire\Snapshot;

use App\Filament\Tables\Columns\CreatedAtColumn;
use App\Filament\Tables\Columns\SnapshotStateColumn;
use App\Models\Snapshot;
use App\Models\SnapshotTransition;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

use function __;
use function view;

class Transitions extends Component implements HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public Snapshot $snapshot;

    public function table(Table $table): Table
    {
        return $table
            ->query(SnapshotTransition::where(['snapshot_id' => $this->snapshot->id]))
            ->columns([
                SnapshotStateColumn::make()
                    ->label(__('snapshot.state')),
                TextColumn::make('creator.name')
                    ->label(__('snapshot_transition.creator')),
                CreatedAtColumn::make()
                    ->sortable(false)
                    ->toggleable(false),
            ])
            ->paginated(false);
    }

    public function render(): View
    {
        return view('livewire.filament.table');
    }
}
