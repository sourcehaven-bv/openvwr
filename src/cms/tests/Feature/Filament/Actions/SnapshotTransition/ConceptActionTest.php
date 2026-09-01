<?php

declare(strict_types=1);

use App\Filament\Actions\SnapshotTransition\ConceptAction;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use App\Models\States\SnapshotState;

it('can make the action', function (): void {
    $snapshot = Snapshot::factory()->create();
    $snapshotState = SnapshotState::make(Concept::$name, $snapshot);
    $conceptAction = ConceptAction::makeForSnapshotState($snapshot, $snapshotState);

    expect($conceptAction)
        ->toBeInstanceOf(ConceptAction::class);
});
