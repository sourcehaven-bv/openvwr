<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use App\Enums\Snapshot\SnapshotDataSection;
use App\Models\Contracts\SnapshotSource;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use App\ValueObjects\Markdown;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function sprintf;

readonly class SnapshotDataMarkdownRenderer
{
    /**
     * @param array<string, array<string, string>> $renderTemplates
     */
    public function __construct(
        private Factory $view,
        private array $renderTemplates,
    ) {
    }

    public function fromSnapshotMarkdown(Snapshot $snapshot, ?Markdown $markdown, SnapshotDataSection $snapshotDataSection): string
    {
        $markdown = Str::of((string) $markdown?->toString());

        Assert::keyExists($this->renderTemplates, $snapshotDataSection->value);
        $templates = $this->renderTemplates[$snapshotDataSection->value];

        foreach ($templates as $model => $viewTemplate) {
            Assert::subclassOf($model, SnapshotSource::class);
            $templateTag = sprintf('<!--- #%s# --->', $model);

            if (!$markdown->contains($templateTag)) {
                continue;
            }

            $relatedSnapshots = $this->getRelatedSnapshots($snapshot, $model);
            $relatedSnapshotsMarkdown = $this->view->make($viewTemplate, ['snapshots' => $relatedSnapshots])->render();

            $markdown = $markdown->replace($templateTag, $relatedSnapshotsMarkdown);
        }

        return $markdown->markdown()->toString();
    }

    /**
     * @param class-string<SnapshotSource> $snapshotSource
     *
     * @return Collection<int, SnapshotSource>
     */
    private function getRelatedSnapshots(Snapshot $snapshot, string $snapshotSource): Collection
    {
        $relatedSnapshots = new Collection();

        $relatedSnapshotSources = $snapshot->relatedSnapshotSources
            ->where('snapshot_source_type', $snapshotSource);

        /** @var RelatedSnapshotSource $relatedSnapshotSource */
        foreach ($relatedSnapshotSources as $relatedSnapshotSource) {
            // The polymorphic target carries no foreign key, so a hard-deleted
            // source leaves an orphan row that resolves to null.
            $source = $relatedSnapshotSource->snapshotSource;
            if ($source === null) {
                continue;
            }

            $relatedSnapshot = $source->getLatestSnapshotWithState([Established::class]);
            if ($relatedSnapshot === null) {
                continue;
            }

            $relatedSnapshots->push($relatedSnapshot);
        }

        return $relatedSnapshots;
    }
}
