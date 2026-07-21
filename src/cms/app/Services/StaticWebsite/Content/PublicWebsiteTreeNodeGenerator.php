<?php

declare(strict_types=1);

namespace App\Services\StaticWebsite\Content;

use App\Models\PublicWebsiteTree;
use App\Services\OrganisationPublishableRecordsService;
use App\Services\StaticWebsite\Generator;
use App\Services\StaticWebsite\PathGenerator;
use App\Services\StaticWebsite\StaticWebsiteFilesystem;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Arr;
use JsonException;

use function array_merge;
use function count;
use function is_resource;

class PublicWebsiteTreeNodeGenerator extends Generator
{
    public function __construct(
        private readonly OrganisationPublishableRecordsService $organisationPublishableRecordsService,
        private readonly PathGenerator $pathGenerator,
        private readonly PublishableGenerator $publishableGenerator,
        private readonly StaticWebsiteFilesystem $staticWebsiteFilesystem,
        private readonly Factory $viewFactory,
    ) {
    }

    /**
     * @param array<string> $parentSlugs
     *
     * @throws JsonException
     */
    public function generate(PublicWebsiteTree $publicWebsiteTree, array $parentSlugs): void
    {
        $path = Arr::join(array_merge($parentSlugs, [$publicWebsiteTree->slug]), '/');

        $this->copyPoster($publicWebsiteTree, $path);

        $contents = $this->viewFactory->make('static-website.public-website-tree', [
            'frontmatter' => $this->convertToFrontmatter([
                'title' => $publicWebsiteTree->title,
                'type' => 'organisation',
                'depth' => count($parentSlugs),
                'public_url' => $publicWebsiteTree->public_url,
            ]),
            'content' => $this->convertMarkdownToHtml($publicWebsiteTree->public_website_content),
            'publicWebsiteTree' => $publicWebsiteTree,
        ])->render();

        $this->staticWebsiteFilesystem->write($this->pathGenerator->getStaticWebsiteTreePath($path), $contents);

        if ($publicWebsiteTree->organisation === null) {
            return;
        }

        $publishableRecords = $this->organisationPublishableRecordsService->getPublishableRecords($publicWebsiteTree->organisation);
        foreach ($publishableRecords as $publishableRecord) {
            $this->publishableGenerator->generate($publishableRecord, $parentSlugs);
        }
    }

    private function copyPoster(PublicWebsiteTree $publicWebsiteTree, string $path): void
    {
        $posterImage = $publicWebsiteTree->getFilamentPoster();
        if ($posterImage === null) {
            return;
        }

        $posterImageStream = $posterImage->stream();
        if (!is_resource($posterImageStream)) {
            return;
        }

        $this->staticWebsiteFilesystem->writeStream($this->pathGenerator->getStaticWebsiteTreePosterPath($path), $posterImageStream);
    }
}
