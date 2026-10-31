<?php

declare(strict_types=1);

namespace App\Services\StaticWebsite\Publication;

use App\Models\Contracts\Publishable;
use App\Models\StaticWebsiteSnapshotEntry;
use App\Services\DateFormatService;

use function __;

class PublicationHistory
{
    /**
     * One line per publication period, newest first, naming the version that
     * was online.
     *
     * @return list<string>
     */
    public function forRecord(Publishable $publishable): array
    {
        $entries = StaticWebsiteSnapshotEntry::query()
            ->whereIn('snapshot_id', $publishable->snapshots()->toBase()->select('id'))
            ->with('snapshot')
            ->orderBy('start_date', 'desc')
            ->get();

        $items = [];
        foreach ($entries as $entry) {
            $parameters = [
                'version' => (string) $entry->snapshot->version,
                'start' => DateFormatService::toDateTime($entry->start_date) ?? '',
            ];

            if ($entry->end_date === null) {
                $items[] = __('static_website.public_from_section.public_history_since', $parameters);

                continue;
            }

            $items[] = __('static_website.public_from_section.public_history_from_to', [
                ...$parameters,
                'end' => DateFormatService::toDateTime($entry->end_date) ?? '',
            ]);
        }

        return $items;
    }
}
