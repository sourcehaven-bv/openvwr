<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Events\StaticWebsite\BuildEvent;
use App\Models\StaticWebsiteSnapshotEntry;
use App\Observers\PublishableObserver;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Webmozart\Assert\Assert;

/**
 * @property CarbonImmutable|null $public_from
 */
trait IsPublishable
{
    final public static function bootIsPublishable(): void
    {
        static::observe(PublishableObserver::class);
    }

    final public function getPublicIdentifier(): string
    {
        return $this->getNumber();
    }

    final public function getPublicFrom(): ?CarbonImmutable
    {
        return $this->public_from;
    }

    final public function isPublished(): bool
    {
        $staticWebsiteSnapshotEntry = $this->getLatestStaticWebsiteSnapshotEntry();

        if ($staticWebsiteSnapshotEntry === null) {
            return false;
        }

        return $staticWebsiteSnapshotEntry->end_date === null;
    }

    /**
     * The entry visitors see now, or else the most recent one. While a new
     * version is being published the open entry still belongs to an older
     * snapshot, so this looks across all of the record's snapshots.
     */
    final public function getLatestStaticWebsiteSnapshotEntry(): ?StaticWebsiteSnapshotEntry
    {
        return StaticWebsiteSnapshotEntry::query()
            ->whereIn('snapshot_id', $this->snapshots()->toBase()->select('id'))
            ->orderByRaw('end_date IS NULL DESC')
            ->orderBy('start_date', 'desc')
            ->first();
    }

    final public function shouldBePublished(): bool
    {
        if ($this->public_from === null) {
            return false;
        }

        return $this->public_from->isPast();
    }

    final public function observeUpdated(): void
    {
        $originalPublicFrom = $this->getOriginal('public_from');
        Assert::nullOrIsInstanceOf($originalPublicFrom, CarbonInterface::class);
        $originalPublicFromIsPast = $originalPublicFrom !== null && $originalPublicFrom->isPast();

        $currentPublicFrom = $this->public_from;
        $currentPublicFromIsPast = $currentPublicFrom !== null && $currentPublicFrom->isPast();

        if ($currentPublicFromIsPast && !$originalPublicFromIsPast) {
            $this->dispatchBuildEvent();
            return;
        }

        if (!$currentPublicFromIsPast && $originalPublicFromIsPast) {
            $this->dispatchBuildEvent();
        }
    }

    final public function observeDeleted(): void
    {
        if ($this->public_from !== null && $this->public_from->isPast()) {
            $this->dispatchBuildEvent();
        }
    }

    private function dispatchBuildEvent(): void
    {
        Log::debug('build event triggered by isPublishable observer');

        BuildEvent::dispatch();
    }
}
