<?php

declare(strict_types=1);

use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Snapshot;
use App\Models\SnapshotData;
use App\Models\States\Snapshot\Established;
use App\Services\OrganisationPublishableRecordsService;
use App\Services\Snapshot\SnapshotDataFactory;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\App;

/**
 * Creates snapshot data for a freshly made record of the given type.
 *
 * @param class-string<AvgProcessorProcessingRecord|AvgResponsibleProcessingRecord> $recordClass
 */
function createSnapshotDataForRecord(string $recordClass, Organisation $organisation): SnapshotData
{
    $record = $recordClass::factory()
        ->recycle($organisation)
        ->create();

    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($record, 'snapshotSource')
        ->create();

    /** @var SnapshotDataFactory $snapshotDataFactory */
    $snapshotDataFactory = App::make(SnapshotDataFactory::class);

    return $snapshotDataFactory->createDataForSnapshot($snapshot);
}

it('generates a public part when publishing is enabled', function (): void {
    config()->set('features.publishing', true);

    $snapshotData = createSnapshotDataForRecord(
        AvgResponsibleProcessingRecord::class,
        Organisation::factory()->create(),
    );

    expect($snapshotData->public_markdown)->not->toBeNull();
    expect($snapshotData->public_frontmatter)->not->toBe([]);
    expect($snapshotData->private_markdown)->not->toBeNull();
});

it('generates no public part when publishing is disabled', function (): void {
    config()->set('features.publishing', false);

    $snapshotData = createSnapshotDataForRecord(
        AvgResponsibleProcessingRecord::class,
        Organisation::factory()->create(),
    );

    // Everything ends up in the private part; the public part is explicitly
    // absent rather than an empty rendered string.
    expect($snapshotData->public_markdown)->toBeNull();
    expect($snapshotData->public_frontmatter)->toBe([]);
    expect($snapshotData->private_markdown)->not->toBeNull();
});

it('leaves a snapshot without a public part out of the publishable records', function (): void {
    config()->set('features.publishing', false);

    $organisation = Organisation::factory()->create([
        'public_from' => CarbonImmutable::yesterday(),
    ]);
    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'public_from' => CarbonImmutable::yesterday(),
        ]);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($record, 'snapshotSource')
        ->create([
            'state' => Established::class,
        ]);

    /** @var SnapshotDataFactory $snapshotDataFactory */
    $snapshotDataFactory = App::make(SnapshotDataFactory::class);
    $snapshotDataFactory->createDataForSnapshot($snapshot);

    /** @var OrganisationPublishableRecordsService $service */
    $service = App::make(OrganisationPublishableRecordsService::class);

    // The record would otherwise qualify: it has a publication date and an
    // established snapshot. The absent public markdown is what excludes it.
    expect($service->getPublishableRecords($organisation->refresh())->count())->toBe(0);
});

it('does not touch snapshots that already have a public part', function (): void {
    $organisation = Organisation::factory()->create();

    config()->set('features.publishing', true);
    $before = createSnapshotDataForRecord(AvgResponsibleProcessingRecord::class, $organisation);
    $publicMarkdown = $before->public_markdown;

    config()->set('features.publishing', false);
    createSnapshotDataForRecord(AvgResponsibleProcessingRecord::class, $organisation);

    // Switching the flag off is a render-time rule for new snapshots only:
    // the stored row of the earlier snapshot is untouched.
    expect($before->refresh()->public_markdown?->toString())
        ->toBe($publicMarkdown?->toString());
});

it('renders the publication date in the private markdown when publishing is enabled', function (
    string $recordClass,
): void {
    config()->set('features.publishing', true);

    $snapshotData = createSnapshotDataForRecord($recordClass, Organisation::factory()->create());

    expect($snapshotData->private_markdown?->toString())
        ->toContain(__('general.public_from'));
})->with('recordsWithPublicationDate');

it('omits the publication date from the private markdown when publishing is disabled', function (
    string $recordClass,
): void {
    config()->set('features.publishing', false);

    $snapshotData = createSnapshotDataForRecord($recordClass, Organisation::factory()->create());

    expect($snapshotData->private_markdown?->toString())
        ->not->toContain(__('general.public_from'));
})->with('recordsWithPublicationDate');

dataset('recordsWithPublicationDate', [
    'responsible processing record' => [AvgResponsibleProcessingRecord::class],
    'processor processing record' => [AvgProcessorProcessingRecord::class],
]);
