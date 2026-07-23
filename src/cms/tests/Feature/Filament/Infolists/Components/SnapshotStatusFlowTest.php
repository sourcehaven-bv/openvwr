<?php

declare(strict_types=1);

use App\Filament\Infolists\Components\SnapshotStatusFlow;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\Obsolete;
use App\Models\States\SnapshotState;
use Tests\Helpers\Model\OrganisationTestHelper;

it('marks the current station as reached even without a recorded transition', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => Established::class,
        ]);

    $flow = SnapshotStatusFlow::buildFlow($snapshot);

    $established = collect($flow['stations'])
        ->firstWhere('current', true);

    expect($established)->not->toBeNull()
        ->and($established['reached'])->toBeTrue();
});

it('marks the obsolete branch as reached and current when obsolete', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => Obsolete::class,
        ]);

    $flow = SnapshotStatusFlow::buildFlow($snapshot);

    expect($flow['obsolete'])->not->toBeNull()
        ->and($flow['obsolete']['reached'])->toBeTrue()
        ->and($flow['obsolete']['current'])->toBeTrue()
        // No main-line station is current when the snapshot is obsolete.
        ->and(collect($flow['stations'])->firstWhere('current', true))->toBeNull();
});

it('only marks the first station reached for a fresh in-review snapshot', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => SnapshotState::DEFAULT_STATE,
        ]);

    $flow = SnapshotStatusFlow::buildFlow($snapshot);

    $reached = collect($flow['stations'])->where('reached', true);

    expect($reached)->toHaveCount(1)
        ->and($flow['stations'][0]['reached'])->toBeTrue()
        ->and($flow['stations'][0]['current'])->toBeTrue();
});
