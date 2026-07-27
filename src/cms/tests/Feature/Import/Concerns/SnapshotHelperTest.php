<?php

declare(strict_types=1);

use App\Components\Uuid\Uuid;
use App\Config\Config;
use App\Import\Factories\Concerns\SnapshotHelper;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Receiver;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use App\Services\Snapshot\SnapshotFactory;
use App\ValueObjects\CalendarDate;
use Tests\Helpers\ConfigTestHelper;

it('creates no snapshot if no configured state found', function (): void {
    ConfigTestHelper::set('import.value_converters.snapshot_state', []);
    $id = fake()->uuid();

    $factory = new class {
        use SnapshotHelper;

        public function test(string $id): void
        {
            $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()->create([
                'id' => Uuid::fromString($id),
            ]);

            $this->createSnapshot($avgResponsibleProcessingRecord, 1, fake()->word(), app(SnapshotFactory::class));
        }
    };
    $factory->test($id);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::query()
        ->where(['id' => $id])
        ->firstOrFail();

    expect($avgResponsibleProcessingRecord->snapshots()->count())
        ->toBe(0);
});

it('creates snapshot and sets review_at', function (): void {
    $state = fake()->word();
    $reviewAtDefaultInMonths = fake()->numberBetween(1, 9);

    ConfigTestHelper::set('import.value_converters.snapshot_state', [
        $state => Established::class,
    ]);
    $id = fake()->uuid();

    $factory = new class {
        use SnapshotHelper;

        public function test(string $id, string $state, int $reviewAtDefaultInMonths): void
        {
            $organisation = Organisation::factory()
                ->create([
                    'review_at_default_in_months' => $reviewAtDefaultInMonths,
                ]);
            $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
                ->for($organisation)
                ->create([
                    'id' => Uuid::fromString($id),
                    'review_at' => null,
                ]);

            $this->createSnapshot($avgResponsibleProcessingRecord, 1, $state, app(SnapshotFactory::class));
        }
    };
    $factory->test($id, $state, $reviewAtDefaultInMonths);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::query()
        ->where(['id' => $id])
        ->firstOrFail();

    // Mirror the implementation: review_at is derived from updated_at in the
    // display timezone, not from now() in UTC. Deriving it any other way makes
    // this test fail whenever the two disagree about the calendar day, which
    // for Europe/Amsterdam is every night between 22:00 and 24:00 UTC.
    $expectedReviewAt = CalendarDate::createFromFormat(
        'Y-m-d',
        $avgResponsibleProcessingRecord->updated_at
            ->setTimezone(Config::string('app.display_timezone'))
            ->floorDay()
            ->addMonths($reviewAtDefaultInMonths)
            ->format('Y-m-d'),
    );

    expect($avgResponsibleProcessingRecord->snapshots()->count())
        ->toBe(1)
        ->and($avgResponsibleProcessingRecord->refresh()->review_at->equalTo($expectedReviewAt))
        ->toBeTrue();
});

it('skips related snapshot sources that have been hard-deleted', function (): void {
    $organisation = Organisation::factory()->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->for($organisation)
        ->create();

    // The polymorphic target has no foreign key, so hard-deleting the source
    // leaves an orphan row. Creating related snapshots must skip it rather
    // than dereference null.
    $receiver = Receiver::factory()
        ->hasAttached($avgResponsibleProcessingRecord)
        ->recycle($organisation)
        ->create();

    // SnapshotFactory rebuilds related rows from the live relation, which drops
    // hard-deleted records. Insert the orphan directly onto the snapshot that
    // createRelatedSnapshots() iterates, then remove the record it points at.
    $factory = new class {
        use SnapshotHelper;

        public function test(Snapshot $snapshot, SnapshotFactory $snapshotFactory): void
        {
            $this->createRelatedSnapshots($snapshot, Established::class, $snapshotFactory);
        }
    };

    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->create();

    RelatedSnapshotSource::factory()->create([
        'snapshot_id' => $snapshot->id,
        'snapshot_source_id' => $receiver->id,
        'snapshot_source_type' => Receiver::class,
    ]);

    Receiver::query()->whereKey($receiver->id)->forceDelete();

    $factory->test($snapshot->fresh(), app(SnapshotFactory::class));

    // The orphan row is skipped, so no snapshot is created for the receiver.
    expect(Snapshot::query()->where('snapshot_source_id', $receiver->id)->count())->toBe(0);
});
