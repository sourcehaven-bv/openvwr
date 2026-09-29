<?php

declare(strict_types=1);

use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\States\Snapshot\Obsolete;
use App\Models\System;
use App\Services\Snapshot\SnapshotComparisonService;
use App\Services\Snapshot\SnapshotFactory;
use App\ValueObjects\CalendarDate;

it('reports no changes between two captures of an untouched record', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create();

    $first = app(SnapshotFactory::class)->fromSnapshotSource($record->refresh(), Obsolete::class);
    $second = app(SnapshotFactory::class)->fromSnapshotSource($record->refresh(), Obsolete::class);

    expect(app(SnapshotComparisonService::class)->hasChanges($first, $second))->toBeFalse();
});

it('reports a change when the stored content differs', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create();

    $first = app(SnapshotFactory::class)->fromSnapshotSource($record->refresh(), Obsolete::class);

    $record->name = 'Een andere naam';
    $record->save();

    $second = app(SnapshotFactory::class)->fromSnapshotSource($record->refresh(), Obsolete::class);

    expect(app(SnapshotComparisonService::class)->hasChanges($first, $second))->toBeTrue();
});

// The many-to-many links live outside the stored markdown, which only holds an inert
// placeholder tag — so comparing the markdown alone would miss a whole system being
// attached.
// The private part is compared separately from the public one, so a field that only
// appears in the private markdown has to be caught on its own. review_at is such a field:
// it is on the private template and absent from the public one.
it('reports a change when only the private part differs', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create([
        'review_at' => CalendarDate::parse('2027-01-01'),
    ]);

    $first = app(SnapshotFactory::class)->fromSnapshotSource($record->refresh(), Obsolete::class);

    $record->review_at = CalendarDate::parse('2028-06-30');
    $record->save();

    $second = app(SnapshotFactory::class)->fromSnapshotSource($record->refresh(), Obsolete::class);

    expect(app(SnapshotComparisonService::class)->hasChanges($first, $second))->toBeTrue();
});

it('reports a change when a related entity is added', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create();

    $first = app(SnapshotFactory::class)->fromSnapshotSource($record->refresh(), Obsolete::class);

    $record->systems()->attach(System::factory()->recycle($record->organisation)->create());

    $second = app(SnapshotFactory::class)->fromSnapshotSource($record->refresh(), Obsolete::class);

    expect(app(SnapshotComparisonService::class)->hasChanges($first, $second))->toBeTrue();
});

// Only the entities themselves matter, not the order they happen to come back in, so a
// reattachment in a different order must not read as a change.
it('reports no change when the same related entities are captured in another order', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create();

    $systems = System::factory()->count(2)->recycle($record->organisation)->create();
    $record->systems()->attach($systems);

    $first = app(SnapshotFactory::class)->fromSnapshotSource($record->refresh(), Obsolete::class);

    $record->systems()->detach();
    $record->systems()->attach($systems->reverse());

    $second = app(SnapshotFactory::class)->fromSnapshotSource($record->refresh(), Obsolete::class);

    expect(app(SnapshotComparisonService::class)->hasChanges($first, $second))->toBeFalse();
});
