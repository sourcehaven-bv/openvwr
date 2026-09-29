<?php

declare(strict_types=1);

namespace App\Livewire\Snapshot;

use App\Filament\Resources\RelatedSnapshotSourceResource;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

use function view;

class RelatedSnapshotSources extends Component implements HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public Snapshot $snapshot;

    public function table(Table $table): Table
    {
        return RelatedSnapshotSourceResource::table($table)
            ->query(RelatedSnapshotSource::where(['snapshot_id' => $this->snapshot->id]));
    }

    public function render(): View
    {
        return view('livewire.filament.table');
    }
}
