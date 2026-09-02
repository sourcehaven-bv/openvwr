<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Keeps every entity's concept snapshot in step with the entity itself.
 *
 * There is always a version: saving an entity writes its concept snapshot, so the
 * user never has to press a button to get one. The concept is a moving target rather
 * than a frozen record — a second save updates the same snapshot instead of piling up
 * a new one — which is why the entity's own snapshot list stays readable and the
 * unique index on (source, state) is never violated.
 */
readonly class ConceptSnapshotService
{
    public function __construct(
        private SnapshotFactory $snapshotFactory,
    ) {
    }

    /**
     * @param Model&SnapshotSource $snapshotSource
     *
     * @throws Throwable
     */
    public function storeConcept(SnapshotSource $snapshotSource): Snapshot
    {
        $concept = $this->findConcept($snapshotSource);

        if ($concept === null) {
            return $this->snapshotFactory->fromSnapshotSource($snapshotSource, Concept::class);
        }

        return $this->snapshotFactory->refreshSnapshot($concept, $snapshotSource);
    }

    private function findConcept(SnapshotSource $snapshotSource): ?Snapshot
    {
        return $snapshotSource->getSnapshotsWithState(Concept::class)->first();
    }
}
