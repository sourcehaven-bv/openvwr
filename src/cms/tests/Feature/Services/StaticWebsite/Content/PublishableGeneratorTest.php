<?php

declare(strict_types=1);

use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Receiver;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\Established;
use App\Services\StaticWebsite\Content\PublishableGenerator;
use App\Services\StaticWebsite\StaticWebsiteFilesystem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

it('will write to the filesystem', function (): void {
    $organisation = Organisation::factory()
        ->create([
            'public_from' => null,
        ]);
    /** @var AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord */
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'public_from' => CarbonImmutable::yesterday(),
        ]);
    Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->withSnapshotData([
            'public_markdown' => fake()->sentence(),
        ])
        ->create([
            'state' => Established::class,
        ]);

    $this->mock(StaticWebsiteFilesystem::class)
        ->shouldReceive('write')
        ->once()
        ->withSomeOfArgs(sprintf('organisatie//verwerkingen/%s.html', Str::slug($avgResponsibleProcessingRecord->getPublicIdentifier())));

    $publishableGenerator = $this->app->get(PublishableGenerator::class);
    $publishableGenerator->generate($avgResponsibleProcessingRecord, []);
});

it('will write to the filesystem in a subfolder', function (): void {
    $organisation = Organisation::factory()
        ->create([
            'public_from' => null,
        ]);
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'public_from' => CarbonImmutable::yesterday(),
        ]);
    Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->withSnapshotData([
            'public_markdown' => fake()->sentence(),
        ])
        ->create([
            'state' => Established::class,
        ]);

    $parentSlug = fake()->slug();
    $this->mock(StaticWebsiteFilesystem::class)
        ->shouldReceive('write')
        ->once()
        ->withSomeOfArgs(
            sprintf('organisatie/%s//verwerkingen/%s.html', $parentSlug, Str::slug($avgResponsibleProcessingRecord->getPublicIdentifier())),
        );

    $publishableGenerator = $this->app->get(PublishableGenerator::class);
    $publishableGenerator->generate($avgResponsibleProcessingRecord, [$parentSlug]);
});

it('will not write to the filesystem if no snapshot available', function (): void {
    $this->mock(StaticWebsiteFilesystem::class)
        ->shouldReceive('write')
        ->never();

    $organisation = Organisation::factory()
        ->create([
            'public_from' => null,
        ]);
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'public_from' => CarbonImmutable::yesterday(),
        ]);

    $publishableGenerator = $this->app->get(PublishableGenerator::class);
    $publishableGenerator->generate($avgResponsibleProcessingRecord, []);
});

it('will not write to the filesystem if no snapshot-data available', function (): void {
    $this->mock(StaticWebsiteFilesystem::class)
        ->shouldReceive('write')
        ->never();

    $organisation = Organisation::factory()
        ->create([
            'public_from' => null,
        ]);
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'public_from' => CarbonImmutable::yesterday(),
        ]);
    Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->create([
            'state' => Established::class,
        ]);

    $publishableGenerator = $this->app->get(PublishableGenerator::class);
    $publishableGenerator->generate($avgResponsibleProcessingRecord, []);
});

it('adds only established receivers to the public record details', function (): void {
    $organisation = Organisation::factory()->create(['public_from' => null]);
    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['public_from' => CarbonImmutable::yesterday()]);
    $snapshot = Snapshot::factory()
        ->for($record, 'snapshotSource')
        ->recycle($organisation)
        ->withSnapshotData([
            'public_markdown' => 'Inhoud',
            'public_frontmatter' => ['record' => ['reference' => 'A-1', 'details' => ['summary' => 'Uitleg']]],
        ])
        ->create(['state' => Established::class]);

    $approved = Receiver::factory()->recycle($organisation)->create();
    Snapshot::factory()
        ->for($approved, 'snapshotSource')
        ->recycle($organisation)
        ->withSnapshotData(['public_markdown' => 'Parkeer\\-handhaving & co'])
        ->create(['state' => Established::class]);
    $draft = Receiver::factory()->recycle($organisation)->create();
    Snapshot::factory()
        ->for($draft, 'snapshotSource')
        ->recycle($organisation)
        ->withSnapshotData(['public_markdown' => 'Niet goedgekeurd'])
        ->create(['state' => Concept::class]);
    $empty = Receiver::factory()->recycle($organisation)->create();
    Snapshot::factory()
        ->for($empty, 'snapshotSource')
        ->recycle($organisation)
        ->withSnapshotData(['public_markdown' => ''])
        ->create(['state' => Established::class]);
    foreach ([$approved, $draft, $empty] as $receiver) {
        RelatedSnapshotSource::factory()->create([
            'snapshot_id' => $snapshot->id,
            'snapshot_source_id' => $receiver->id,
            'snapshot_source_type' => Receiver::class,
        ]);
    }

    $written = null;
    $this->mock(StaticWebsiteFilesystem::class)
        ->shouldReceive('write')
        ->once()
        ->withArgs(static function (string $path, string $content) use (&$written): bool {
            $written = $content;

            return true;
        });

    $this->app->get(PublishableGenerator::class)->generate($record, []);

    expect($written)->toContain('"details":{"summary":"Uitleg","receivers":["Parkeer-handhaving & co"]}');
});

it('leaves records without public details as they are', function (): void {
    $organisation = Organisation::factory()->create(['public_from' => null]);
    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['public_from' => CarbonImmutable::yesterday()]);
    Snapshot::factory()
        ->for($record, 'snapshotSource')
        ->recycle($organisation)
        ->withSnapshotData([
            'public_markdown' => 'Inhoud',
            'public_frontmatter' => ['record' => ['reference' => 'A-1']],
        ])
        ->create(['state' => Established::class]);

    $written = null;
    $this->mock(StaticWebsiteFilesystem::class)
        ->shouldReceive('write')
        ->once()
        ->withArgs(static function (string $path, string $content) use (&$written): bool {
            $written = $content;

            return true;
        });

    $this->app->get(PublishableGenerator::class)->generate($record, []);

    expect($written)->toContain('"record":{"reference":"A-1"}')
        ->not->toContain('receivers');
});
