<?php

declare(strict_types=1);

namespace Tests\Feature\Models\States;

use App\Filament\Actions\DataBreachRecordTransition\CloseAction;
use App\Filament\Actions\DataBreachRecordTransition\MarkAsNoBreachAction;
use App\Filament\Actions\DataBreachRecordTransition\ReportAction;
use App\Filament\Actions\DataBreachRecordTransition\RespondAction;
use App\Filament\Actions\DataBreachRecordTransition\VerifyAction;
use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecord\Closed;
use App\Models\States\DataBreachRecord\InResponse;
use App\Models\States\DataBreachRecord\NoBreach;
use App\Models\States\DataBreachRecord\Reported;
use App\Models\States\DataBreachRecord\Verified;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

use function expect;
use function it;

it('defaults to reported', function (): void {
    $dataBreachRecord = DataBreachRecord::factory()->create();

    expect($dataBreachRecord->state)->toBeInstanceOf(Reported::class);
});

it('returns the correct action', function (string $state, string $expectedAction): void {
    $dataBreachRecord = DataBreachRecord::factory()->inState($state)->create();

    expect($dataBreachRecord->state->getAction())
        ->toBe($expectedAction);
})->with([
    [Reported::class, ReportAction::class],
    [Verified::class, VerifyAction::class],
    [InResponse::class, RespondAction::class],
    [Closed::class, CloseAction::class],
    [NoBreach::class, MarkAsNoBreachAction::class],
]);

it('allows the transitions', function (string $state, string $newState): void {
    $dataBreachRecord = DataBreachRecord::factory()->inState($state)->create();

    $dataBreachRecord->state->transitionTo($newState);

    expect($dataBreachRecord->state)->toBeInstanceOf($newState);
})->with([
    // Forward through the workflow.
    [Reported::class, Verified::class],
    [Verified::class, InResponse::class],
    [InResponse::class, Closed::class],

    // Concluding there was no breach.
    [Reported::class, NoBreach::class],
    [Verified::class, NoBreach::class],
    [InResponse::class, NoBreach::class],

    // Corrections and reopening.
    [Verified::class, Reported::class],
    [InResponse::class, Verified::class],
    [Closed::class, InResponse::class],
    [NoBreach::class, Reported::class],
]);

it('does not allow skipping steps', function (string $state, string $newState): void {
    $dataBreachRecord = DataBreachRecord::factory()->inState($state)->create();

    $dataBreachRecord->state->transitionTo($newState);

    expect($dataBreachRecord->state)
        ->toBeInstanceOf($newState);
})->throws(TransitionNotFound::class)->with([
    [Reported::class, InResponse::class],
    [Reported::class, Closed::class],
    [Verified::class, Closed::class],
    [InResponse::class, Reported::class],
    [Closed::class, Verified::class],
    [Closed::class, Reported::class],
    [Closed::class, NoBreach::class],
    [NoBreach::class, Verified::class],
    [NoBreach::class, InResponse::class],
    [NoBreach::class, Closed::class],
]);
