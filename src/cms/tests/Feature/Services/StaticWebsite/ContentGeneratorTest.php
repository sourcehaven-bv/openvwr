<?php

declare(strict_types=1);

use App\Models\PublicWebsiteTree;
use App\Services\StaticWebsite\Content\HomeGenerator;
use App\Services\StaticWebsite\Content\PublicWebsiteTreeListGenerator;
use App\Services\StaticWebsite\Content\PublicWebsiteTreeNodeGenerator;
use App\Services\StaticWebsite\Content\SitemapGenerator;
use App\Services\StaticWebsite\ContentGenerator;

it('will call the correct generators on generate', function (): void {
    $this->mock(HomeGenerator::class)
        ->shouldReceive('generate')
        ->once();

    $this->mock(PublicWebsiteTreeListGenerator::class)
        ->shouldReceive('generate')
        ->once();

    $this->mock(SitemapGenerator::class)
        ->shouldReceive('generate')
        ->once();

    $contentGenerator = $this->app->get(ContentGenerator::class);
    $contentGenerator->generate();
});

it('skips website tree children without a past public from date', function (): void {
    $root = PublicWebsiteTree::factory()->create(['parent_id' => null, 'public_from' => '2026-01-01', 'organisation_id' => null]);
    $publicChild = PublicWebsiteTree::factory()->create(
        ['parent_id' => $root->id, 'public_from' => '2026-01-01', 'organisation_id' => null],
    );
    PublicWebsiteTree::factory()->create(['parent_id' => $root->id, 'public_from' => null, 'organisation_id' => null]);
    PublicWebsiteTree::factory()->create(['parent_id' => $root->id, 'public_from' => '2999-01-01', 'organisation_id' => null]);

    $generated = [];
    $this->mock(PublicWebsiteTreeNodeGenerator::class)
        ->shouldReceive('generate')
        ->andReturnUsing(static function (PublicWebsiteTree $node) use (&$generated): void {
            $generated[] = $node->id->toString();
        });

    $this->app->get(ContentGenerator::class)->generate();

    expect($generated)->toBe([$root->id->toString(), $publicChild->id->toString()]);
});
