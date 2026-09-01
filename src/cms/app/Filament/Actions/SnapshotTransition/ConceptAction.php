<?php

declare(strict_types=1);

namespace App\Filament\Actions\SnapshotTransition;

use App\Models\Snapshot;
use App\Models\States\SnapshotState;

class ConceptAction extends SnapshotTransitionAction
{
    public static function makeForSnapshotState(Snapshot $snapshot, SnapshotState $snapshotState): static
    {
        return parent::makeForSnapshotState($snapshot, $snapshotState);
    }
}
