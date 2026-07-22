<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Transfer\ModelGraph;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use function is_array;
use function is_string;

/**
 * Re-links imported entities once every record exists in the destination organisation:
 * pivot relations first, then self-referencing parent links.
 */
class RelationRestorer
{
    /**
     * @param array<string, Model> $idMap destination model per bundle uuid
     * @param array<string, true> $written bundle uuids created or overwritten by this import
     */
    public function restore(TransferBundle $bundle, array $idMap, array $written): void
    {
        foreach ($bundle->entities as $id => $entity) {
            if (!isset($written[$id])) {
                continue;
            }

            $this->restoreRelations($idMap[$id], $entity['relations'] ?? null, $idMap);
            $this->restoreParentLink($idMap[$id], $entity['attributes'] ?? null, $idMap);
        }
    }

    /**
     * @param array<string, Model> $idMap
     */
    private function restoreRelations(Model $model, mixed $relations, array $idMap): void
    {
        if (!is_array($relations)) {
            return;
        }

        foreach ($relations as $relationName => $relatedIds) {
            if (!is_string($relationName) || !is_array($relatedIds)) {
                continue;
            }

            $relation = ModelGraph::relation($model, $relationName);

            if (!$relation instanceof BelongsToMany) {
                continue;
            }

            $mappedIds = $this->mapIds($relatedIds, $idMap);

            if ($mappedIds !== []) {
                $relation->syncWithoutDetaching($mappedIds);
            }
        }
    }

    /**
     * @param array<mixed> $relatedIds
     * @param array<string, Model> $idMap
     *
     * @return list<string>
     */
    private function mapIds(array $relatedIds, array $idMap): array
    {
        $mappedIds = [];

        foreach ($relatedIds as $relatedId) {
            $mapped = is_string($relatedId) ? ($idMap[$relatedId] ?? null) : null;

            if ($mapped !== null) {
                $mappedIds[] = ModelGraph::id($mapped);
            }
        }

        return $mappedIds;
    }

    /**
     * @param array<string, Model> $idMap
     */
    private function restoreParentLink(Model $model, mixed $attributes, array $idMap): void
    {
        $parentId = is_array($attributes) ? ($attributes['parent_id'] ?? null) : null;

        if (!is_string($parentId) || !isset($idMap[$parentId])) {
            return;
        }

        $model->setAttribute('parent_id', ModelGraph::id($idMap[$parentId]));
        $model->save();
    }
}
