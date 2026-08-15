<?php

declare(strict_types=1);

use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\ContactPerson;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\System;

it('can load the related snapshot sources', function (): void {
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create();
    $relatedSnapshotSources = $avgResponsibleProcessingRecord->getRelatedSnapshotSources();
    expect($relatedSnapshotSources->keys()->toArray())
        ->toBe([
            AlgorithmRecord::class,
            ContactPerson::class,
            Processor::class,
            Receiver::class,
            Responsible::class,
            System::class,
        ]);
});

it('can load with state on deleted snapshot sources', function (): void {
    $snapshotState = fake()->randomElement([
        Approved::class,
        Established::class,
        InReview::class,
    ]);

    $organisation = Organisation::factory()
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'deleted_at' => fake()->dateTime(),
        ]);
    Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => $snapshotState,
        ]);

    $snapshotsWithState = $avgResponsibleProcessingRecord->getSnapshotsWithState($snapshotState);
    expect($snapshotsWithState)
        ->toHaveCount(1);
});
