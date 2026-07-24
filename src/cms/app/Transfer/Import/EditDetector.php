<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Transfer\ModelGraph;
use App\Transfer\TransferEntityType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use function array_keys;
use function in_array;

/**
 * Decides whether an existing copy has been edited locally since it was last written
 * by a cross-org copy. `last_synced_at` is the watermark of "when this row last matched
 * its source"; a copy counts as edited when it — or any of its relation links — changed
 * after that moment. Relation pivots carry their own timestamps (withTimestamps) but do
 * not touch the parent, so both signals are needed.
 */
class EditDetector
{
    public function isEditedSinceSync(Model $model): bool
    {
        $lastSyncedAt = $model->getAttribute('last_synced_at');

        // Never synced (matched by name, or a copy predating this feature): treat as edited
        // so the user is asked rather than silently overwritten.
        if (!$lastSyncedAt instanceof CarbonInterface) {
            return true;
        }

        $updatedAt = $model->getAttribute('updated_at');

        if ($updatedAt instanceof CarbonInterface && $updatedAt->gt($lastSyncedAt)) {
            return true;
        }

        return $this->hasRelationEditedSince($model, $lastSyncedAt);
    }

    private function hasRelationEditedSince(Model $model, CarbonInterface $lastSyncedAt): bool
    {
        foreach (array_keys(TransferEntityType::SELECTABLE_RELATIONS) as $relationName) {
            $relation = ModelGraph::relation($model, $relationName);

            // Only pivots that carry timestamps can report when they last changed. A few
            // relations (tags, data-breach links) have timestamp-less pivots, so changes to
            // those cannot be detected this way and are treated as not-an-edit.
            if (!$relation instanceof BelongsToMany || !in_array('updated_at', $relation->getPivotColumns(), true)) {
                continue;
            }

            $touched = $relation
                ->wherePivot('updated_at', '>', $lastSyncedAt)
                ->exists();

            if ($touched) {
                return true;
            }
        }

        return false;
    }
}
