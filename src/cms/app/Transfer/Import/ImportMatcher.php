<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Models\Organisation;
use App\Transfer\TransferEntityType;
use Illuminate\Database\Eloquent\Model;

use function is_array;
use function is_string;

class ImportMatcher
{
    /**
     * Find existing content in the destination organisation for a bundle entity:
     * first by origin_id (stable identity stamped by a previous import/export),
     * then by the type's natural name column.
     *
     * @param array<string, mixed> $entity
     */
    public function match(TransferEntityType $type, array $entity, Organisation $organisation): ?Model
    {
        if ($type->isOwned() || $type->isLookup()) {
            return null;
        }

        return $this->matchByOriginId($type, $entity, $organisation)
            ?? $this->matchByName($type, $entity, $organisation);
    }

    /**
     * @param array<string, mixed> $entity
     */
    private function matchByOriginId(TransferEntityType $type, array $entity, Organisation $organisation): ?Model
    {
        $originId = $entity['origin_id'] ?? null;

        if (!is_string($originId) || $originId === '') {
            return null;
        }

        return $type->modelClass()::query()
            ->whereBelongsTo($organisation)
            ->where('origin_id', $originId)
            ->first();
    }

    /**
     * @param array<string, mixed> $entity
     */
    private function matchByName(TransferEntityType $type, array $entity, Organisation $organisation): ?Model
    {
        $matchColumn = $type->matchColumn();
        $attributes = $entity['attributes'] ?? null;

        if ($matchColumn === null || !is_array($attributes)) {
            return null;
        }

        $matchValue = $attributes[$matchColumn] ?? null;

        if (!is_string($matchValue) || $matchValue === '') {
            return null;
        }

        return $type->modelClass()::query()
            ->whereBelongsTo($organisation)
            ->where($matchColumn, $matchValue)
            ->first();
    }
}
