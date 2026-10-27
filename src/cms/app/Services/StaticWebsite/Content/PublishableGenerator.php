<?php

declare(strict_types=1);

namespace App\Services\StaticWebsite\Content;

use App\Enums\Snapshot\SnapshotDataSection;
use App\Models\Contracts\Publishable;
use App\Models\Receiver;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use App\Services\Snapshot\SnapshotDataMarkdownRenderer;
use App\Services\StaticWebsite\Generator;
use App\Services\StaticWebsite\PathGenerator;
use App\Services\StaticWebsite\StaticWebsiteFilesystem;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use function array_merge;
use function html_entity_decode;
use function is_array;
use function strip_tags;
use function trim;

class PublishableGenerator extends Generator
{
    public function __construct(
        private readonly PathGenerator $pathGenerator,
        private readonly StaticWebsiteFilesystem $staticWebsiteFilesystem,
        private readonly SnapshotDataMarkdownRenderer $snapshotDataMarkdownRenderer,
        private readonly Factory $viewFactory,
    ) {
    }

    /**
     * @param array<string> $parentSlugs
     */
    public function generate(Publishable $publishable, array $parentSlugs): void
    {
        $snapshot = $publishable->getLatestSnapshotWithState([Established::class]);
        if ($snapshot === null) {
            return;
        }

        $snapshotData = $snapshot->snapshotData;
        if ($snapshotData === null) {
            return;
        }

        $frontmatterData = array_merge(
            ['id' => $snapshot->id->toString()],
            $snapshotData->public_frontmatter,
        );
        $record = $frontmatterData['record'] ?? null;
        if (is_array($record) && is_array($record['details'] ?? null)) {
            $record['details']['receivers'] = $this->getReceivers($snapshot);
            $frontmatterData['record'] = $record;
        }


        $content = $this->viewFactory->make('static-website.processing-record', [
            'frontmatter' => $this->convertToFrontmatter($frontmatterData),
            'markdown' => $this->snapshotDataMarkdownRenderer->fromSnapshotMarkdown(
                $snapshot,
                $snapshotData->public_markdown,
                SnapshotDataSection::PUBLIC,
            ),
        ])->render();

        $path = Arr::join(array_merge($parentSlugs, [$publishable->getOrganisation()->publicWebsiteTree?->slug]), '/');
        $publishablePath = $this->pathGenerator->getPublishablePath($path, Str::slug($publishable->getPublicIdentifier()));
        $this->staticWebsiteFilesystem->write($publishablePath, $content);
    }

    /**
     * The receivers as plain text, from their latest established snapshot: a
     * receiver has its own approval, so one that was never approved stays out.
     *
     * @return array<int, string>
     */
    private function getReceivers(Snapshot $snapshot): array
    {
        $receivers = [];
        foreach ($this->snapshotDataMarkdownRenderer->getRelatedSnapshots($snapshot, Receiver::class) as $receiverSnapshot) {
            $html = (string) $receiverSnapshot->snapshotData?->public_markdown?->toHtml();
            $text = trim(html_entity_decode(strip_tags($html)));
            if ($text !== '') {
                $receivers[] = $text;
            }
        }

        return $receivers;
    }
}
