<?php

declare(strict_types=1);

namespace Tests\Feature\Faker;

use App\Models\ContactPerson;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use App\Models\States\SnapshotState;

use function expect;
use function fake;
use function it;

it('returns obsolete if none other available', function (): void {
    $state = fake()->snapshotState(SnapshotState::all()->toArray());
    $snapshot = Snapshot::factory()
        ->create([
            'state' => $state,
        ]);

    expect($snapshot->state)
        ->toBeInstanceOf(Obsolete::class);
});

it('each state can only occur once, except obsolete', function (): void {
    $state = fake()->snapshotState();
    $amountOfSnapshots = fake()->numberBetween(25, 50);
    $contactPerson = ContactPerson::factory()
        ->create();

    $snapshots = Snapshot::factory()
        ->for($contactPerson, 'snapshotSource')
        ->count($amountOfSnapshots)
        ->create([
            'state' => $state,
        ]);

    $snapshotStateCount = $snapshots->countBy(static function (Snapshot $snapshot): string {
        return $snapshot->state->getValue();
    })->toArray();

    expect($snapshotStateCount[Concept::$name])->toBe(1)
        ->and($snapshotStateCount[InReview::$name])->toBe(1)
        ->and($snapshotStateCount[Established::$name])->toBe(1)
        ->and($snapshotStateCount[Approved::$name])->toBe(1)
        ->and($snapshotStateCount[Obsolete::$name])->toBe($amountOfSnapshots - 4);
});
