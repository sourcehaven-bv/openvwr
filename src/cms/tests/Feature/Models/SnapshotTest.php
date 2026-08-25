<?php

declare(strict_types=1);

use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Snapshot;
use App\Models\SnapshotTransition;
use Carbon\CarbonImmutable;

it('belongs to an organisation', function (): void {
    $snapshot = Snapshot::factory()->create();

    expect($snapshot->organisation)
        ->toBeInstanceOf(Organisation::class);
});

it('can hold all snapshot states', function ($snapshotState): void {
    $snapshot = Snapshot::factory()->create([
        'state' => $snapshotState,
    ]);

    expect((string) $snapshot->state)
        ->toBe($snapshotState);
})->with(Snapshot::getStates()->toArray());

it('will return the snapshotSource', function (): void {
    $organisation = Organisation::factory()
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create();

    expect($snapshot->snapshotSource->id)
        ->toBe($avgResponsibleProcessingRecord->id);
});

it('will not return the snapshotSource if deleted', function (): void {
    $organisation = Organisation::factory()
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'deleted_at' => fake()->dateTime(),
        ]);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create();

    expect($snapshot->snapshotSource)
        ->toBeNull();
});

it('returns its transitions oldest first', function (): void {
    $snapshot = Snapshot::factory()->create();

    $newer = SnapshotTransition::factory()->recycle($snapshot)->create();
    $newer->forceFill(['created_at' => CarbonImmutable::create(2024, 5, 1)])->saveQuietly();

    $older = SnapshotTransition::factory()->recycle($snapshot)->create();
    $older->forceFill(['created_at' => CarbonImmutable::create(2024, 1, 1)])->saveQuietly();

    expect($snapshot->snapshotTransitions->pluck('id')->map->toString()->all())
        ->toBe([$older->id->toString(), $newer->id->toString()]);
});

// Zonder ON DELETE CASCADE weigert de FK de delete van de organisatie in
// plaats van de snapshot mee te nemen -- dat blokkeerde het definitief
// opruimen van een organisatie.
it('is deleted along with its organisation', function (): void {
    $organisation = Organisation::factory()->create();
    $snapshot = Snapshot::factory()->recycle($organisation)->create();

    $organisation->forceDelete();

    expect(Snapshot::query()->whereKey($snapshot->id)->exists())
        ->toBeFalse();
});

// De onderliggende tabellen cascaden al richting `snapshots`; deze test legt
// vast dat die keten ook vanaf de organisatie doorloopt.
it('takes its transitions with it when the organisation is deleted', function (): void {
    $organisation = Organisation::factory()->create();
    $snapshot = Snapshot::factory()->recycle($organisation)->create();
    $transition = SnapshotTransition::factory()->recycle($snapshot)->create();

    $organisation->forceDelete();

    expect(SnapshotTransition::query()->whereKey($transition->id)->exists())
        ->toBeFalse();
});
