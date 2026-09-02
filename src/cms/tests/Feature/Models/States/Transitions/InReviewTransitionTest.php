<?php

declare(strict_types=1);

namespace Tests\Feature\Models\States\Transitions;

use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\InReview;
use App\Models\User;

use function expect;
use function it;

it('can transition to in review', function (): void {
    $this->be(User::factory()->create());

    $snapshot = Snapshot::factory()->create([
        'state' => Concept::class,
    ]);
    $snapshot->state->transitionTo(InReview::class);
    $snapshot->refresh();

    expect($snapshot->state)
        ->toBeInstanceOf(InReview::class);
});
