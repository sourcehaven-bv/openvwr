<?php

declare(strict_types=1);

namespace App\Models\Contracts;

use App\Models\Organisation;
use App\Models\Snapshot;
use App\Models\States\SnapshotState;
use App\Models\StaticWebsiteSnapshotEntry;
use Carbon\CarbonImmutable;

interface Publishable extends SnapshotSource
{
    public function getOrganisation(): Organisation;

    public function getPublicIdentifier(): string;

    public function getPublicFrom(): ?CarbonImmutable;

    public function isPublished(): bool;

    public function getLatestStaticWebsiteSnapshotEntry(): ?StaticWebsiteSnapshotEntry;

    /**
     * @param array<class-string<SnapshotState>> $snapshotStates
     */
    public function getLatestSnapshotWithState(array $snapshotStates): ?Snapshot;

    public function shouldBePublished(): bool;

    public function observeUpdated(): void;

    public function observeDeleted(): void;
}
