<?php

declare(strict_types=1);

namespace App\Services\StaticWebsite\Publication;

use App\Models\Contracts\Publishable;
use App\Models\PublicWebsiteTree;
use App\Models\States\Snapshot\Established;
use App\Models\StaticWebsiteCheck;
use App\Models\StaticWebsiteSnapshotEntry;
use App\Services\OrganisationPublishableRecordsService;
use Carbon\CarbonImmutable;

use function array_shift;

/**
 * Site-wide view of the last publication: when it was checked, how many
 * records it confirmed, and which records should be online but are not.
 */
class PublicationOverview
{
    public function __construct(
        private readonly OrganisationPublishableRecordsService $organisationPublishableRecordsService,
        private readonly PublicationStatusResolver $publicationStatusResolver,
    ) {
    }

    public function getLatestCheckDate(): ?CarbonImmutable
    {
        return StaticWebsiteCheck::query()->orderBy('build_date', 'desc')->first()?->build_date;
    }

    public function getConfirmedCount(): int
    {
        return StaticWebsiteSnapshotEntry::query()->whereNull('end_date')->count();
    }

    /**
     * Records the next build will publish whose latest established version the
     * last check did not confirm.
     *
     * @return list<array{record: Publishable, status: PublicationStatus}>
     */
    public function getUnconfirmedRecords(): array
    {
        /** @var array<string, true> $confirmedSnapshotIds */
        $confirmedSnapshotIds = StaticWebsiteSnapshotEntry::query()
            ->whereNull('end_date')
            ->get(['snapshot_id'])
            ->mapWithKeys(static fn (StaticWebsiteSnapshotEntry $entry): array => [$entry->snapshot_id->toString() => true])
            ->all();

        $unconfirmed = [];
        foreach ($this->getPublicTreeNodes() as $node) {
            if ($node->organisation === null) {
                continue;
            }

            foreach ($this->organisationPublishableRecordsService->getPublishableRecords($node->organisation) as $record) {
                $snapshot = $record->getLatestSnapshotWithState([Established::class]);
                if ($snapshot !== null && isset($confirmedSnapshotIds[$snapshot->id->toString()])) {
                    continue;
                }

                $unconfirmed[] = [
                    'record' => $record,
                    'status' => $this->publicationStatusResolver->resolve($record),
                ];
            }
        }

        return $unconfirmed;
    }

    /**
     * The tree nodes the content generator publishes: public roots and, below
     * them, every public child.
     *
     * @return list<PublicWebsiteTree>
     */
    private function getPublicTreeNodes(): array
    {
        $nodes = [];
        $pending = PublicWebsiteTree::whereNull('parent_id')
            ->where('public_from', '<=', CarbonImmutable::now())
            ->get()
            ->all();

        while ($pending !== []) {
            $node = array_shift($pending);
            $nodes[] = $node;

            foreach ($node->children as $child) {
                if ($child->isPublic()) {
                    $pending[] = $child;
                }
            }
        }

        return $nodes;
    }
}
