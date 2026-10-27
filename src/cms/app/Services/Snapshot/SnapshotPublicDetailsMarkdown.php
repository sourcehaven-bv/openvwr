<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use App\Models\Snapshot;
use Illuminate\Contracts\View\Factory;

use function is_array;

/**
 * Renders the structured public fields of a snapshot (`record.details` in the
 * public frontmatter) as markdown, so an approver sees everything a theme can
 * put on the public website, not only the public markdown.
 */
readonly class SnapshotPublicDetailsMarkdown
{
    public function __construct(
        private Factory $view,
    ) {
    }

    public function render(Snapshot $snapshot): ?string
    {
        $record = $snapshot->snapshotData?->public_frontmatter['record'] ?? null;
        $details = is_array($record) ? $record['details'] ?? null : null;
        if (!is_array($details)) {
            return null;
        }

        return $this->view->make('snapshot-data-render.public-details', ['details' => $details])->render();
    }
}
