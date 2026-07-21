<?php

declare(strict_types=1);

namespace App\Transfer\Export;

use App\Transfer\ModelGraph;
use App\Transfer\TransferEntityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RelatedItemCollector
{
    /**
     * Collect the related items of the given records, grouped per relation, for the export form.
     *
     * @param Collection<int|string, Model> $records
     *
     * @return array<string, array{type: TransferEntityType, options: array<string, string>}>
     */
    public function collect(Collection $records): array
    {
        $groups = [];

        foreach (TransferEntityType::SELECTABLE_RELATIONS as $relationName => $relatedType) {
            $options = [];

            foreach ($records as $record) {
                foreach (ModelGraph::related($record, $relationName) as $related) {
                    $options[ModelGraph::id($related)] = $relatedType->displayName($related);
                }
            }

            if ($options === []) {
                continue;
            }

            $groups[$relationName] = [
                'type' => $relatedType,
                'options' => $options,
            ];
        }

        return $groups;
    }
}
