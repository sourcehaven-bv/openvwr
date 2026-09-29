<?php

declare(strict_types=1);

use App\Jobs\StaticWebsite\ContentGeneratorJob;
use App\Jobs\StaticWebsite\HugoWebsiteGeneratorJob;
use App\Jobs\StaticWebsite\StaticWebsiteCheckJob;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\PublicWebsiteTree;
use App\Models\Snapshot;
use App\Models\SnapshotData;
use App\Models\States\Snapshot\Established;
use Illuminate\Support\Facades\Bus;
use Tests\Helpers\ConfigTestHelper;

it('the command will dispatch the correct jobs', function (): void {
    Bus::fake();

    $this->artisan('static-website:refresh')
        ->assertExitCode(0);

    Bus::assertChained([
        ContentGeneratorJob::class,
        HugoWebsiteGeneratorJob::class,
        StaticWebsiteCheckJob::class,
    ]);
});

it('does not dispatch jobs when publishing is disabled', function (): void {
    ConfigTestHelper::set('features.publishing', false);
    Bus::fake();

    $this->artisan('static-website:refresh')
        ->expectsOutputToContain('publishing feature is disabled')
        ->assertSuccessful();

    Bus::assertNothingDispatched();
});

it('can run the command and a published tree node with children, organisation & register', function (): void {
    ConfigTestHelper::set('static-website.static_website_generator', 'fake');

    $organisation1 = Organisation::factory()->create(['public_from' => fake()->date()]);
    $organisation2 = Organisation::factory()->create(['public_from' => fake()->date()]);

    $publicWebsiteTreeParent = PublicWebsiteTree::factory()
        ->recycle($organisation1)
        ->create(['public_from' => fake()->date()]);
    PublicWebsiteTree::factory()
        ->recycle($organisation2)
        ->create([
            'public_from' => fake()->date(),
            'parent_id' => $publicWebsiteTreeParent->id,
        ]);
    PublicWebsiteTree::factory() // add another node without organisation
        ->create([
            'public_from' => fake()->date(),
            'parent_id' => $publicWebsiteTreeParent->id,
        ]);

    $avgResponsibleProcessingRecord1 = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation1)
        ->create(['public_from' => fake()->date()]);
    $avgResponsibleProcessingRecord2 = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation2)
        ->create(['public_from' => fake()->date()]);
    Snapshot::factory()
        ->recycle($organisation1)
        ->has(SnapshotData::factory()->state([
            'public_markdown' => fake()->markdown(),
        ]))
        ->create([
            'snapshot_source_id' => $avgResponsibleProcessingRecord1->id,
            'snapshot_source_type' => $avgResponsibleProcessingRecord1::class,
            'state' => Established::class,
        ]);
    Snapshot::factory()
        ->recycle($organisation2)
        ->has(SnapshotData::factory()->state([
            'public_markdown' => fake()->markdown(),
        ]))
        ->create([
            'snapshot_source_id' => $avgResponsibleProcessingRecord2->id,
            'snapshot_source_type' => $avgResponsibleProcessingRecord2::class,
            'state' => Established::class,
        ]);

    $this->artisan('static-website:refresh')
        ->assertExitCode(0);
});
