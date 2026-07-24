<?php

declare(strict_types=1);

namespace App\Transfer\Export;

use App\Models\Organisation;
use App\Transfer\Import\TransferBundle;
use App\Transfer\TransferEntityType;
use Carbon\CarbonImmutable;

use function array_values;

/**
 * Builds a transfer bundle in memory — the same structure a zip export produces, but
 * without touching disk. Used by the direct cross-org copy, where source and destination
 * live on the same instance so there is nothing to serialize to a file.
 */
class BundleBuilder
{
    public function __construct(
        private readonly EntityGraphCollector $entityGraphCollector,
        private readonly EntitySerializer $entitySerializer,
    ) {
    }

    /**
     * @param list<string> $recordIds
     * @param array<string, list<string>> $selectedRelated selected related ids, keyed by relation name
     */
    public function build(
        TransferEntityType $recordType,
        array $recordIds,
        array $selectedRelated,
        Organisation $sourceOrganisation,
        ?CarbonImmutable $now = null,
    ): TransferBundle {
        $records = $recordType->modelClass()::query()
            ->whereBelongsTo($sourceOrganisation)
            ->whereIn('id', $recordIds)
            ->get();

        $models = $this->entityGraphCollector->collect(array_values($records->all()), $selectedRelated);

        $entities = [];
        $manifestEntities = [];

        foreach ($models as $id => $model) {
            $data = $this->entitySerializer->serialize($model);
            $entities[$id] = $data;
            $manifestEntities[] = [
                'type' => $data['type'],
                'id' => $id,
                'origin_id' => $data['origin_id'],
                'name' => $data['name'],
            ];
        }

        $manifest = [
            'format' => 'openvwr-transfer',
            'version' => 1,
            'exported_at' => ($now ?? CarbonImmutable::now())->toIso8601String(),
            'source_organisation' => [
                'id' => $sourceOrganisation->id->toString(),
                'name' => $sourceOrganisation->name,
            ],
            'entities' => $manifestEntities,
        ];

        return new TransferBundle($manifest, $entities);
    }
}
