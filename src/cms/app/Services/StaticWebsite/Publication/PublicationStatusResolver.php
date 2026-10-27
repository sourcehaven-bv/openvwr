<?php

declare(strict_types=1);

namespace App\Services\StaticWebsite\Publication;

use App\Config\Feature;
use App\Models\Contracts\Publishable;
use App\Models\PublicWebsiteTree;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\StaticWebsiteCheck;
use App\Models\StaticWebsiteSnapshotEntry;

use function max;

/**
 * Explains whether a record is on the public website and, if not, why not.
 *
 * The checks mirror the content generator (ContentGenerator and
 * OrganisationPublishableRecordsService), so a status here predicts what the
 * next build will publish. The publication check (StaticWebsiteSnapshotEntry)
 * then tells whether the site actually shows it.
 */
class PublicationStatusResolver
{
    private ?StaticWebsiteCheck $latestCheck = null;
    private bool $latestCheckLoaded = false;

    public function resolve(Publishable $publishable): PublicationStatus
    {
        if (!Feature::publishingEnabled()) {
            return new PublicationStatus(PublicationState::NOT_PUBLISHING_DISABLED);
        }

        $establishedSnapshot = $publishable->getLatestSnapshotWithState([Established::class]);
        $versionInProgress = $this->getVersionInProgress($publishable, $establishedSnapshot);
        $onlineEntry = $this->getOnlineEntry($publishable);

        $status = $this->resolveState($publishable, $establishedSnapshot, $onlineEntry);

        // Still online, but the next build will leave it out.
        $blocker = null;
        if ($onlineEntry !== null && $status->state->keepsRecordOffline()) {
            $blocker = $status;
            $status = new PublicationStatus(PublicationState::REMOVING);
        }

        return new PublicationStatus(
            state: $status->state,
            date: $status->date,
            url: $onlineEntry?->url,
            onlineVersion: $onlineEntry?->snapshot->version,
            establishedVersion: $establishedSnapshot?->version,
            sectionTitle: $status->sectionTitle,
            versionInProgress: $versionInProgress,
            blocker: $blocker,
        );
    }

    private function resolveState(
        Publishable $publishable,
        ?Snapshot $establishedSnapshot,
        ?StaticWebsiteSnapshotEntry $onlineEntry,
    ): PublicationStatus {
        $publicFrom = $publishable->getPublicFrom();
        if ($publicFrom === null) {
            return new PublicationStatus(PublicationState::NOT_PUBLIC_FROM_MISSING);
        }

        if ($establishedSnapshot === null) {
            return new PublicationStatus(PublicationState::NOT_NO_ESTABLISHED_VERSION);
        }

        if ($establishedSnapshot->snapshotData?->public_markdown === null) {
            return new PublicationStatus(PublicationState::NOT_NO_PUBLIC_CONTENT);
        }

        $websiteTree = $publishable->getOrganisation()->publicWebsiteTree;
        if ($websiteTree === null) {
            return new PublicationStatus(PublicationState::NOT_ORGANISATION_NOT_ON_WEBSITE);
        }

        $publicFromDates = [$publicFrom];
        foreach ($this->getPathToRoot($websiteTree) as $node) {
            if ($node->public_from === null) {
                return new PublicationStatus(PublicationState::NOT_WEBSITE_SECTION_NOT_PUBLIC, sectionTitle: $node->title);
            }

            $publicFromDates[] = $node->public_from;
        }

        $publicAt = max($publicFromDates);
        if ($publicAt->isFuture()) {
            return new PublicationStatus(PublicationState::SCHEDULED, date: $publicAt);
        }

        if ($onlineEntry !== null && $onlineEntry->snapshot_id->equals($establishedSnapshot->id)) {
            return new PublicationStatus(PublicationState::PUBLISHED, date: $onlineEntry->start_date);
        }

        if ($onlineEntry !== null) {
            return new PublicationStatus(PublicationState::UPDATING, date: $onlineEntry->start_date);
        }

        // The record qualified at the latest of: becoming established and each
        // public_from date. A check that ran after that and still did not find
        // the record means the build left it out or failed to upload.
        $qualifiedAt = max($publicAt, $establishedSnapshot->updated_at);
        $latestCheck = $this->getLatestCheck();
        if ($latestCheck !== null && $latestCheck->build_date->greaterThan($qualifiedAt)) {
            return new PublicationStatus(PublicationState::NOT_CONFIRMED, date: $latestCheck->build_date);
        }

        return new PublicationStatus(PublicationState::PENDING);
    }

    /**
     * The entry visitors currently see. During a version change this can still
     * belong to an older snapshot than the latest established one.
     */
    private function getOnlineEntry(Publishable $publishable): ?StaticWebsiteSnapshotEntry
    {
        return StaticWebsiteSnapshotEntry::query()
            ->whereIn('snapshot_id', $publishable->snapshots()->toBase()->select('id'))
            ->whereNull('end_date')
            ->with('snapshot')
            ->first();
    }

    private function getVersionInProgress(Publishable $publishable, ?Snapshot $establishedSnapshot): ?Snapshot
    {
        $snapshot = $publishable->getLatestSnapshotWithState([Concept::class, InReview::class, Approved::class]);
        if ($snapshot === null) {
            return null;
        }

        if ($establishedSnapshot !== null && $snapshot->version < $establishedSnapshot->version) {
            return null;
        }

        return $snapshot;
    }

    /**
     * @return list<PublicWebsiteTree>
     */
    private function getPathToRoot(PublicWebsiteTree $node): array
    {
        $path = [$node];
        while ($node->parent_id !== null && $node->parent !== null) {
            $node = $node->parent;
            $path[] = $node;
        }

        return $path;
    }

    private function getLatestCheck(): ?StaticWebsiteCheck
    {
        if (!$this->latestCheckLoaded) {
            $this->latestCheck = StaticWebsiteCheck::query()->orderBy('build_date', 'desc')->first();
            $this->latestCheckLoaded = true;
        }

        return $this->latestCheck;
    }
}
