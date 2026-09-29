<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use App\Config\Feature;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use Illuminate\Support\Str;

use function __;
use function class_basename;
use function implode;
use function sprintf;

/**
 * Whether two versions hold the same registration.
 *
 * A version is compared through what was stored on it, not through what it renders:
 * SnapshotDataMarkdownRenderer injects the currently-established related snapshots at
 * render time, so its output is not a function of the version and two identical
 * versions could still render differently.
 *
 * The same three parts the compare page diffs make up the comparison — the public
 * markdown, the private markdown, and the captured related entities — so "geen
 * wijzigingen" here means the same thing as an empty compare page.
 */
readonly class SnapshotComparisonService
{
    public function hasChanges(Snapshot $from, Snapshot $to): bool
    {
        // Without publishing there is no public part to compare. Versions taken before
        // the flag was switched off still carry stored public markdown, so comparing it
        // anyway would report a change in what we render rather than in the registration.
        if (Feature::publishingEnabled()) {
            $fromPublicMarkdown = $from->snapshotData?->public_markdown?->toString();
            $toPublicMarkdown = $to->snapshotData?->public_markdown?->toString();

            if ($fromPublicMarkdown !== $toPublicMarkdown) {
                return true;
            }
        }

        $fromPrivateMarkdown = $from->snapshotData?->private_markdown?->toString();
        $toPrivateMarkdown = $to->snapshotData?->private_markdown?->toString();

        if ($fromPrivateMarkdown !== $toPrivateMarkdown) {
            return true;
        }

        return $this->getRelatedSourcesText($from) !== $this->getRelatedSourcesText($to);
    }

    /**
     * The captured related entities as a stable text block.
     *
     * The many-to-many links (systems, processors, receivers, ...) live in
     * `related_snapshot_sources` rather than in the stored markdown, which only holds an
     * inert placeholder tag that is identical across versions. Comparing the markdown
     * alone would therefore report "geen wijzigingen" even when a whole system was added
     * or removed.
     *
     * Only the entity's identity is used, and both levels are sorted, so a merely
     * reordered or recapitalised capture does not count as a change.
     */
    public function getRelatedSourcesText(Snapshot $snapshot): string
    {
        $entries = $snapshot->relatedSnapshotSources
            ->toBase()
            /** @return array{type: string, name: string, sort: string}|null */
            ->map(static function (RelatedSnapshotSource $relatedSnapshotSource): ?array {
                // The morph target has no foreign key (uuidMorphs indexes only), so a
                // hard-deleted source leaves an orphan row resolving to null. Skipping it
                // keeps both sides consistent rather than throwing here.
                $snapshotSource = $relatedSnapshotSource->snapshotSource;

                if ($snapshotSource === null) {
                    return null;
                }

                $name = $snapshotSource->getDisplayName();

                return [
                    'type' => __(sprintf(
                        '%s.model_singular',
                        Str::snake(class_basename($relatedSnapshotSource->snapshot_source_type)),
                    )),
                    'name' => $name,
                    'sort' => Str::lower($name),
                ];
            })
            ->filter();

        $lines = [];
        $currentType = null;

        foreach ($entries->sortBy(['type', 'sort'])->all() as $entry) {
            if ($entry['type'] !== $currentType) {
                $currentType = $entry['type'];
                $lines[] = $currentType . ':';
            }

            $lines[] = sprintf('  - %s', $entry['name']);
        }

        return implode("\n", $lines);
    }
}
