<?php

declare(strict_types=1);

namespace App\Transfer\Export;

use App\Models\Document;
use App\Models\Stakeholder;
use App\Transfer\ModelGraph;
use App\Transfer\TransferEntityType;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function array_keys;
use function is_string;
use function sprintf;

class EntitySerializer
{
    /**
     * Attributes that never leave the source organisation: identity, tenant scoping,
     * numbering, publication/review state and timestamps are all rebuilt on import.
     */
    public const array EXCLUDED_ATTRIBUTES = [
        'id',
        'organisation_id',
        'entity_number_id',
        'number',
        'origin_id',
        'import_id',
        'last_synced_at',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'public_from',
        'published_at',
        'review_at',
    ];

    private const array OWNER_MORPH_NAMES = [
        'addressable',
        'remark_relatable',
        'fg_remark_relatable',
    ];

    /**
     * @return array<string, mixed>
     */
    public function serialize(Model $model): array
    {
        $type = TransferEntityType::fromModel($model);
        $id = ModelGraph::id($model);
        $originId = $model->getAttribute('origin_id');

        $data = [
            'type' => $type->value,
            'id' => $id,
            'origin_id' => is_string($originId) ? $originId : $id,
            'name' => $type->displayName($model),
            'attributes' => $this->serializeAttributes($model),
        ];

        $owner = $this->serializeOwner($model);
        if ($owner !== null) {
            $data['owner'] = $owner;
        }

        $relations = $this->serializeRelations($model);
        if ($relations !== []) {
            $data['relations'] = $relations;
        }

        if ($model instanceof Document) {
            $data['media'] = $this->serializeMedia($model);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeAttributes(Model $model): array
    {
        $attributes = $model->getAttributes();

        foreach (self::EXCLUDED_ATTRIBUTES as $excluded) {
            unset($attributes[$excluded]);
        }

        foreach (self::OWNER_MORPH_NAMES as $morphName) {
            unset($attributes[sprintf('%s_type', $morphName)], $attributes[sprintf('%s_id', $morphName)]);
        }

        return $attributes;
    }

    /**
     * @return array{type: string, id: string}|null
     */
    private function serializeOwner(Model $model): ?array
    {
        $rawAttributes = $model->getAttributes();

        foreach (self::OWNER_MORPH_NAMES as $morphName) {
            $typeColumn = sprintf('%s_type', $morphName);
            $idColumn = sprintf('%s_id', $morphName);

            if (!array_key_exists($typeColumn, $rawAttributes) || !is_string($rawAttributes[$typeColumn])) {
                continue;
            }

            $ownerType = TransferEntityType::tryFromModelClass($rawAttributes[$typeColumn]);
            if ($ownerType === null) {
                continue;
            }

            $ownerId = $rawAttributes[$idColumn] ?? null;
            Assert::string($ownerId);

            return [
                'type' => $ownerType->value,
                'id' => $ownerId,
            ];
        }

        return null;
    }

    /**
     * @return array<string, list<string>>
     */
    private function serializeRelations(Model $model): array
    {
        $relations = [];

        foreach (array_keys(TransferEntityType::SELECTABLE_RELATIONS) as $relationName) {
            $ids = [];
            foreach (ModelGraph::related($model, $relationName) as $related) {
                $ids[] = ModelGraph::id($related);
            }

            if ($ids !== []) {
                $relations[$relationName] = $ids;
            }
        }

        if ($model instanceof Stakeholder) {
            $ids = [];
            foreach (ModelGraph::related($model, 'stakeholderDataItems') as $dataItem) {
                $ids[] = ModelGraph::id($dataItem);
            }

            if ($ids !== []) {
                $relations['stakeholderDataItems'] = $ids;
            }
        }

        return $relations;
    }

    /**
     * @return list<array<string, ?string>>
     */
    private function serializeMedia(Document $document): array
    {
        $media = [];

        foreach ($document->media as $mediaItem) {
            $media[] = [
                'uuid' => $mediaItem->uuid,
                'collection_name' => $mediaItem->collection_name,
                'file_name' => $mediaItem->file_name,
                'name' => $mediaItem->name,
                'mime_type' => $mediaItem->mime_type,
                // sha256 of the file bytes. Carried so a re-copy can tell whether the
                // destination file already matches the source without moving any bytes.
                'content_hash' => $mediaItem->content_hash,
                'zip_path' => sprintf('media/%s/%s', $mediaItem->uuid, $mediaItem->file_name),
            ];
        }

        return $media;
    }
}
