<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Keeps every entity's concept snapshot in step with the entity itself.
 *
 * Saving an entity writes its concept snapshot, so the user never has to press a button
 * to get one. The concept is a moving target rather than a frozen record — a second save
 * updates the same snapshot instead of piling up a new one — which is why the entity's
 * own snapshot list stays readable and the unique index on (source, state) is never
 * violated.
 *
 * A save that records nothing new leaves no concept behind: pressing save on an unaltered
 * form would otherwise put a concept next to a version that says exactly the same thing,
 * and editing back to what the previous version says would leave one that can never be
 * submitted. The comparison is against the newest version other than the concept itself,
 * whatever its state, so an entity identical to its established version keeps just that
 * one — and a concept that is edited back to it is taken away again.
 *
 * An entity with no other version keeps its concept whether or not the save changed
 * anything: it is the only record of the entity there is.
 */
readonly class ConceptSnapshotService
{
    public function __construct(
        private SnapshotFactory $snapshotFactory,
        private SnapshotComparisonService $snapshotComparisonService,
    ) {
    }

    /**
     * Writes the entity's concept, unless it would say the same as the latest version.
     *
     * Returns the concept the entity now has, or null when saving left the versions as
     * they were — either because nothing changed, or because there is nothing to record
     * yet.
     *
     * @param Model&SnapshotSource $snapshotSource
     *
     * @throws Throwable
     */
    public function storeConcept(SnapshotSource $snapshotSource): ?Snapshot
    {
        return DB::transaction(function () use ($snapshotSource): ?Snapshot {
            $concept = $this->findConcept($snapshotSource);

            // The version the concept would follow: the newest one that is not the concept
            // itself. Read before writing, because writing the concept is what makes it the
            // newest.
            $previousSnapshot = $this->findPreviousSnapshot($snapshotSource, $concept);

            $concept = $concept === null
                ? $this->snapshotFactory->fromSnapshotSource($snapshotSource, Concept::class)
                : $this->snapshotFactory->refreshSnapshot($concept, $snapshotSource);

            if ($previousSnapshot === null) {
                return $concept;
            }

            if ($this->snapshotComparisonService->hasChanges($previousSnapshot, $concept)) {
                return $concept;
            }

            // Identical to the version it follows, so it says nothing that version does not
            // already say. That holds just as much for a concept that was already there and
            // has now been edited back: keeping it would leave a version the user cannot
            // submit and that shows no changes when compared.
            $concept->delete();

            return null;
        });
    }

    private function findConcept(SnapshotSource $snapshotSource): ?Snapshot
    {
        return $snapshotSource->getSnapshotsWithState(Concept::class)->first();
    }

    /**
     * The newest version the concept would follow, whatever its state.
     *
     * State is deliberately not filtered on. What matters is whether this save records
     * anything the version history does not already hold, and a version says the same
     * thing whether it is established, under review or withdrawn.
     *
     * @param Model&SnapshotSource $snapshotSource
     */
    private function findPreviousSnapshot(SnapshotSource $snapshotSource, ?Snapshot $concept): ?Snapshot
    {
        // Compared on the string, not with Model::is(): the key is a Uuid value object, so
        // the identity comparison behind is() never holds for two separately loaded rows.
        $conceptId = $concept?->id->toString();

        return $snapshotSource->snapshots()
            ->get()
            ->reject(static function (Snapshot $snapshot) use ($conceptId): bool {
                return $snapshot->id->toString() === $conceptId;
            })
            ->sortByDesc('version')
            ->first();
    }
}
