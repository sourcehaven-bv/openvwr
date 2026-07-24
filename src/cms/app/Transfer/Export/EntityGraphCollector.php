<?php

declare(strict_types=1);

namespace App\Transfer\Export;

use App\Models\Stakeholder;
use App\Transfer\ModelGraph;
use App\Transfer\TransferEntityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use function array_keys;
use function array_shift;
use function in_array;
use function is_string;

/**
 * Walks the transferable graph of a set of records: the records themselves, the selected
 * related items, and everything those depend on (lookup values referenced by foreign keys,
 * plus owned entities — address, remarks, FG remark, stakeholder data items). This is the
 * single source of truth for "what travels with a record", shared by the zip export and the
 * in-memory cross-org copy.
 */
class EntityGraphCollector
{
    /**
     * @param list<Model> $records
     * @param array<string, list<string>> $selectedRelated selected related ids, keyed by relation name
     *
     * @return array<string, Model> all graph entities, keyed by uuid
     */
    public function collect(array $records, array $selectedRelated): array
    {
        /** @var array<string, Model> $entities */
        $entities = [];
        $queue = $this->seed($records, $selectedRelated);

        while ($queue !== []) {
            $model = array_shift($queue);
            $id = ModelGraph::id($model);

            if (isset($entities[$id])) {
                continue;
            }

            $entities[$id] = $model;

            foreach ($this->expand($model) as $dependency) {
                $queue[] = $dependency;
            }
        }

        return $entities;
    }

    /**
     * @param list<Model> $records
     * @param array<string, list<string>> $selectedRelated
     *
     * @return list<Model>
     */
    private function seed(array $records, array $selectedRelated): array
    {
        $queue = $records;

        foreach ($records as $record) {
            foreach (array_keys(TransferEntityType::SELECTABLE_RELATIONS) as $relationName) {
                $selectedIds = $selectedRelated[$relationName] ?? [];

                foreach (ModelGraph::related($record, $relationName) as $related) {
                    if (in_array(ModelGraph::id($related), $selectedIds, true)) {
                        $queue[] = $related;
                    }
                }
            }
        }

        return $queue;
    }

    /**
     * @return list<Model>
     */
    private function expand(Model $model): array
    {
        return [
            ...$this->referencedByForeignKey($model),
            ...$this->ownedEntities($model),
        ];
    }

    /**
     * Lookup values and other transferable entities referenced by a `<type>_id` column.
     *
     * @return list<Model>
     */
    private function referencedByForeignKey(Model $model): array
    {
        $referenced = [];

        foreach ($model->getAttributes() as $column => $value) {
            if (!is_string($value) || !Str::endsWith($column, '_id')) {
                continue;
            }

            $referencedType = TransferEntityType::tryFrom(Str::beforeLast($column, '_id'));

            if ($referencedType === null) {
                continue;
            }

            $referencedModel = $referencedType->modelClass()::query()->find($value);

            if ($referencedModel !== null) {
                $referenced[] = $referencedModel;
            }
        }

        return $referenced;
    }

    /**
     * Entities that belong to the model itself: address, remarks, FG remark and
     * (for stakeholders) their data items.
     *
     * @return list<Model>
     */
    private function ownedEntities(Model $model): array
    {
        $owned = [
            ...ModelGraph::related($model, 'remarks'),
            ...($model instanceof Stakeholder ? ModelGraph::related($model, 'stakeholderDataItems') : []),
        ];

        foreach (['address', 'fgRemark'] as $relationName) {
            $related = ModelGraph::relatedOne($model, $relationName);

            if ($related !== null) {
                $owned[] = $related;
            }
        }

        return $owned;
    }
}
