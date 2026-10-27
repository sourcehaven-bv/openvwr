<?php

declare(strict_types=1);

use App\Jobs\StaticWebsite\StaticWebsiteCheckContentProcessorJob;
use App\Models\Snapshot;
use App\Models\StaticWebsiteCheck;
use App\Models\StaticWebsiteSnapshotEntry;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Psr\Log\NullLogger;

it('reads the content and creates a static-website snapshot-entry', function (): void {
    $snapshot = Snapshot::factory()->create();

    $staticWebsiteCheck = StaticWebsiteCheck::factory()
        ->create([
            'content' => [
                'pages' => [
                    [
                        'id' => $snapshot->id->toString(),
                        'permalink' => fake()->url(),
                        'type' => 'processing-record',
                    ],
                ],
            ],
        ]);

    $this->assertDatabaseCount(StaticWebsiteSnapshotEntry::class, 0);

    $staticWebsiteCheckContentProcessorJob = new StaticWebsiteCheckContentProcessorJob($staticWebsiteCheck);
    $staticWebsiteCheckContentProcessorJob->handle(new NullLogger());

    $this->assertDatabaseCount(StaticWebsiteSnapshotEntry::class, 1);
});

it('also updates the url on an existing entry', function (): void {
    $snapshot = Snapshot::factory()->create();

    $url = fake()->url();
    $staticWebsiteCheck = StaticWebsiteCheck::factory()
        ->create([
            'build_date' => CarbonImmutable::now(),
            'content' => [
                'pages' => [
                    [
                        'id' => $snapshot->id->toString(),
                        'permalink' => $url,
                        'type' => 'processing-record',
                    ],
                ],
            ],
        ]);

    $existingEntry = StaticWebsiteSnapshotEntry::factory()
        ->create([
            'last_static_website_check_id' => StaticWebsiteCheck::factory()->create()->id,
            'snapshot_id' => $snapshot->id,
            'url' => fake()->url(),
            'end_date' => null,
        ]);

    $staticWebsiteCheckContentProcessorJob = new StaticWebsiteCheckContentProcessorJob($staticWebsiteCheck);
    $staticWebsiteCheckContentProcessorJob->handle(new NullLogger());

    $entries = StaticWebsiteSnapshotEntry::where('snapshot_id', $snapshot->id)->get();
    expect($entries)->toHaveCount(1);

    $entry = $entries->sole();
    expect($entry->id)->toBe($existingEntry->id)
        ->and($entry->url)->toBe($url)
        ->and($entry->start_date->equalTo($existingEntry->start_date))->toBeTrue()
        ->and($entry->end_date)->toBeNull();
});

it('keeps a single entry when the same snapshot is published again', function (): void {
    $snapshot = Snapshot::factory()->create();
    $url = fake()->url();

    foreach (['2026-10-01 10:00:00', '2026-10-02 10:00:00'] as $buildDate) {
        $staticWebsiteCheck = StaticWebsiteCheck::factory()
            ->create([
                'build_date' => CarbonImmutable::parse($buildDate),
                'content' => [
                    'pages' => [
                        [
                            'id' => $snapshot->id->toString(),
                            'permalink' => $url,
                            'type' => 'processing-record',
                        ],
                    ],
                ],
            ]);

        (new StaticWebsiteCheckContentProcessorJob($staticWebsiteCheck))->handle(new NullLogger());
    }

    $entry = StaticWebsiteSnapshotEntry::where('snapshot_id', $snapshot->id)->sole();
    expect($entry->start_date->equalTo(CarbonImmutable::parse('2026-10-01 10:00:00')))->toBeTrue()
        ->and($entry->end_date)->toBeNull();
});

it('skips a check that a newer build has superseded', function (): void {
    $snapshot = Snapshot::factory()->create();

    $olderCheck = StaticWebsiteCheck::factory()
        ->create([
            'build_date' => CarbonImmutable::parse('2026-10-01 10:00:00'),
            'content' => ['pages' => []],
        ]);
    $newerCheck = StaticWebsiteCheck::factory()
        ->create([
            'build_date' => CarbonImmutable::parse('2026-10-02 10:00:00'),
            'content' => [
                'pages' => [
                    [
                        'id' => $snapshot->id->toString(),
                        'permalink' => fake()->url(),
                        'type' => 'processing-record',
                    ],
                ],
            ],
        ]);

    (new StaticWebsiteCheckContentProcessorJob($newerCheck))->handle(new NullLogger());
    (new StaticWebsiteCheckContentProcessorJob($olderCheck))->handle(new NullLogger());

    $entry = StaticWebsiteSnapshotEntry::where('snapshot_id', $snapshot->id)->sole();
    expect($entry->end_date)->toBeNull();
});

it('refuses a second open entry for the same snapshot', function (): void {
    $snapshot = Snapshot::factory()->create();
    StaticWebsiteSnapshotEntry::factory()->create(['snapshot_id' => $snapshot->id, 'end_date' => null]);

    StaticWebsiteSnapshotEntry::factory()->create(['snapshot_id' => $snapshot->id, 'end_date' => null]);
})->throws(UniqueConstraintViolationException::class);

it('marks the end date for a non existing entry if not in content', function (): void {
    $StaticWebsiteSnapshotEntry = StaticWebsiteSnapshotEntry::factory()->create(['end_date' => null]);

    $staticWebsiteCheck = StaticWebsiteCheck::factory()
        ->create([
            'build_date' => CarbonImmutable::now(),
            'content' => [
                'pages' => [],
            ],
        ]);

    $staticWebsiteCheckContentProcessorJob = new StaticWebsiteCheckContentProcessorJob($staticWebsiteCheck);
    $staticWebsiteCheckContentProcessorJob->handle(new NullLogger());

    $StaticWebsiteSnapshotEntry->refresh();
    expect($StaticWebsiteSnapshotEntry->end_date)->not()->toBeNull();
});

it('silently fails on invalid content', function (): void {
    $staticWebsiteCheck = StaticWebsiteCheck::factory()
        ->create([
            'content' => [fake()->word()],
        ]);

    $staticWebsiteCheckContentProcessorJob = new StaticWebsiteCheckContentProcessorJob($staticWebsiteCheck);
    $staticWebsiteCheckContentProcessorJob->handle(new NullLogger());

    $this->assertDatabaseCount(StaticWebsiteSnapshotEntry::class, 0);
});

it('silently fails on invalid page content', function (): void {
    $staticWebsiteCheck = StaticWebsiteCheck::factory()
        ->create([
            'content' => [
                'pages' => [
                    fake()->word(),
                ],
            ],
        ]);

    $staticWebsiteCheckContentProcessorJob = new StaticWebsiteCheckContentProcessorJob($staticWebsiteCheck);
    $staticWebsiteCheckContentProcessorJob->handle(new NullLogger());

    $this->assertDatabaseCount(StaticWebsiteSnapshotEntry::class, 0);
});
