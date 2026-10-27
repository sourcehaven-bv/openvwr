<?php

declare(strict_types=1);

namespace App\Services\StaticWebsite;

use App\Facades\AdminLog;
use App\Models\PublicWebsiteTree;
use App\Services\StaticWebsite\Content\HomeGenerator;
use App\Services\StaticWebsite\Content\PublicWebsiteTreeListGenerator;
use App\Services\StaticWebsite\Content\PublicWebsiteTreeNodeGenerator;
use App\Services\StaticWebsite\Content\PublishableListGenerator;
use App\Services\StaticWebsite\Content\SitemapGenerator;
use Carbon\CarbonImmutable;
use Exception;
use JsonException;

use function microtime;

class ContentGenerator
{
    public function __construct(
        private readonly HomeGenerator $homeGenerator,
        private readonly PublicWebsiteTreeListGenerator $publicWebsiteTreeListGenerator,
        private readonly PublicWebsiteTreeNodeGenerator $publicWebsiteTreeNodeGenerator,
        private readonly PublishableListGenerator $publishableListGenerator,
        private readonly StaticWebsiteFilesystem $staticWebsiteFilesystem,
        private readonly SitemapGenerator $sitemapGenerator,
    ) {
    }

    /**
     * @throws Exception
     */
    public function generate(): void
    {
        AdminLog::log('Generating content files...', [
            'startTime' => microtime(true),
        ]);

        $this->staticWebsiteFilesystem->deleteAll();

        $publicWebsiteTrees = PublicWebsiteTree::whereNull('parent_id')
            ->where('public_from', '<=', CarbonImmutable::now())
            ->orderBy('order')
            ->get();
        foreach ($publicWebsiteTrees as $publicWebsiteTree) {
            $this->addPublicWebsiteTreeGeneratorJob($publicWebsiteTree);
        }

        $this->homeGenerator->generate();
        $this->sitemapGenerator->generate();
        $this->publicWebsiteTreeListGenerator->generate();
    }

    /**
     * @param array<string> $parentSlugs
     *
     * @throws JsonException
     */
    private function addPublicWebsiteTreeGeneratorJob(PublicWebsiteTree $publicWebsiteTree, array $parentSlugs = []): void
    {
        $this->publicWebsiteTreeNodeGenerator->generate($publicWebsiteTree, $parentSlugs);
        $this->publishableListGenerator->generate($publicWebsiteTree, $parentSlugs);

        $parentSlugs[] = $publicWebsiteTree->slug;
        foreach ($publicWebsiteTree->children as $child) {
            // Children honour their own public_from, like the root nodes above.
            if (!$child->isPublic()) {
                continue;
            }

            $this->addPublicWebsiteTreeGeneratorJob($child, $parentSlugs);
        }
    }
}
