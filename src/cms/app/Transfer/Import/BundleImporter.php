<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Models\Document;
use App\Models\Organisation;
use App\Models\User;
use App\Transfer\ConflictStrategy;
use App\Transfer\ModelGraph;
use App\Transfer\TransferEntityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Webmozart\Assert\Assert;

use function __;
use function array_search;
use function is_array;
use function is_string;
use function sprintf;
use function uasort;

class BundleImporter
{
    /**
     * Creation order: foreign-key targets before their dependents, owned entities after their owners.
     * Pivot links and parent links are restored in separate passes afterwards.
     */
    private const array IMPORT_ORDER = [
        TransferEntityType::DOCUMENT_TYPE,
        TransferEntityType::CONTACT_PERSON_POSITION,
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD_SERVICE,
        TransferEntityType::AVG_PROCESSOR_PROCESSING_RECORD_SERVICE,
        TransferEntityType::WPG_PROCESSING_RECORD_SERVICE,
        TransferEntityType::ALGORITHM_THEME,
        TransferEntityType::ALGORITHM_STATUS,
        TransferEntityType::ALGORITHM_PUBLICATION_CATEGORY,
        TransferEntityType::TAG,
        TransferEntityType::AVG_GOAL,
        TransferEntityType::WPG_GOAL,
        TransferEntityType::STAKEHOLDER_DATA_ITEM,
        TransferEntityType::STAKEHOLDER,
        TransferEntityType::PROCESSOR,
        TransferEntityType::RECEIVER,
        TransferEntityType::RESPONSIBLE,
        TransferEntityType::SYSTEM,
        TransferEntityType::CONTACT_PERSON,
        TransferEntityType::DOCUMENT,
        TransferEntityType::DATA_BREACH_RECORD,
        TransferEntityType::ALGORITHM_RECORD,
        TransferEntityType::WPG_PROCESSING_RECORD,
        TransferEntityType::AVG_PROCESSOR_PROCESSING_RECORD,
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        TransferEntityType::ADDRESS,
        TransferEntityType::REMARK,
        TransferEntityType::FG_REMARK,
    ];

    /** @var array<string, Model> destination model per bundle uuid */
    private array $idMap = [];

    /** @var array<string, true> bundle uuids that were created or overwritten */
    private array $written = [];

    public function __construct(
        private readonly BundleReader $bundleReader,
        private readonly ImportMatcher $importMatcher,
        private readonly RelationRestorer $relationRestorer,
        private readonly AttributeRemapper $attributeRemapper,
    ) {
    }

    /**
     * @param array<string, array{selected: bool, strategy: ?string}> $plan
     */
    public function import(string $zipPath, array $plan, Organisation $organisation, User $user): TransferImportResult
    {
        $bundle = $this->bundleReader->read($zipPath);
        $result = new TransferImportResult();

        $this->idMap = [];
        $this->written = [];

        DB::transaction(function () use ($bundle, $plan, $organisation, $user, $zipPath, $result): void {
            foreach ($this->sortedEntities($bundle) as $id => $entity) {
                $type = $this->typeOf($entity);

                if ($type->isLookup()) {
                    $this->idMap[$id] = $this->importLookup($type, $entity, $organisation);
                    continue;
                }

                if ($type->isOwned()) {
                    $this->importOwned($type, $entity, $user);
                    continue;
                }

                $this->importEntity($type, $id, $entity, $plan, $organisation, $zipPath, $result);
            }

            $this->relationRestorer->restore($bundle, $this->idMap, $this->written);
        });

        return $result;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function sortedEntities(TransferBundle $bundle): array
    {
        $entities = $bundle->entities;

        uasort($entities, function (array $left, array $right): int {
            $leftOrder = array_search($this->typeOf($left), self::IMPORT_ORDER, true);
            $rightOrder = array_search($this->typeOf($right), self::IMPORT_ORDER, true);

            return (int) $leftOrder <=> (int) $rightOrder;
        });

        return $entities;
    }

    /**
     * @param array<string, mixed> $entity
     */
    private function typeOf(array $entity): TransferEntityType
    {
        $type = $entity['type'] ?? null;
        Assert::string($type);

        return TransferEntityType::from($type);
    }

    /**
     * @param array<string, mixed> $entity
     */
    private function originIdOf(array $entity): ?string
    {
        $originId = $entity['origin_id'] ?? null;

        return is_string($originId) && $originId !== '' ? $originId : null;
    }

    /**
     * @param array<string, mixed> $entity
     */
    private function importLookup(TransferEntityType $type, array $entity, Organisation $organisation): Model
    {
        $attributes = $this->attributeRemapper->attributes($entity);
        $name = $attributes['name'] ?? null;
        Assert::string($name);

        $existing = $type->modelClass()::query()
            ->whereBelongsTo($organisation)
            ->where('name', $name)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $model = $type->modelClass()::query()->newModelInstance();
        $model->forceFill($attributes);
        $model->setAttribute('organisation_id', $organisation->id->toString());
        $model->save();

        return $model;
    }

    /**
     * @param array<string, array{selected: bool, strategy: ?string}> $plan
     * @param array<string, mixed> $entity
     */
    private function importEntity(
        TransferEntityType $type,
        string $id,
        array $entity,
        array $plan,
        Organisation $organisation,
        string $zipPath,
        TransferImportResult $result,
    ): void {
        $planItem = $plan[$id] ?? null;

        if ($planItem === null || $planItem['selected'] !== true) {
            return;
        }

        $strategy = ConflictStrategy::tryFrom((string) $planItem['strategy']);
        $existing = $this->importMatcher->match($type, $entity, $organisation);

        if ($existing !== null && ($strategy === null || $strategy === ConflictStrategy::SKIP)) {
            $this->skipEntity($id, $existing, $result);

            return;
        }

        if ($existing !== null && $strategy === ConflictStrategy::OVERWRITE) {
            $this->overwriteEntity($id, $entity, $existing, $zipPath, $result);

            return;
        }

        $this->createEntity($type, $id, $entity, $existing, $organisation, $zipPath, $result);
    }

    private function skipEntity(string $id, Model $existing, TransferImportResult $result): void
    {
        $this->idMap[$id] = $existing;
        $result->skipped++;
    }

    /**
     * @param array<string, mixed> $entity
     */
    private function overwriteEntity(
        string $id,
        array $entity,
        Model $existing,
        string $zipPath,
        TransferImportResult $result,
    ): void {
        $existing->forceFill($this->attributeRemapper->remap($entity, $this->idMap));

        if ($existing->getAttribute('origin_id') === null) {
            $existing->setAttribute('origin_id', $this->originIdOf($entity));
        }

        $existing->save();

        $this->idMap[$id] = $existing;
        $this->written[$id] = true;
        $result->overwritten++;

        if ($existing instanceof Document && $existing->media->isEmpty()) {
            $this->importMedia($existing, $entity, $zipPath);
        }
    }

    /**
     * Creates a new record. When $existing is given the user chose "add a copy",
     * so the name is suffixed and no origin_id is claimed (that stays with the original).
     *
     * @param array<string, mixed> $entity
     */
    private function createEntity(
        TransferEntityType $type,
        string $id,
        array $entity,
        ?Model $existing,
        Organisation $organisation,
        string $zipPath,
        TransferImportResult $result,
    ): void {
        $attributes = $this->attributeRemapper->remap($entity, $this->idMap);
        $attributes = $existing === null
            ? [...$attributes, 'origin_id' => $this->originIdOf($entity)]
            : $this->suffixCopy($attributes, $type);

        $model = $type->modelClass()::query()->newModelInstance();
        $model->forceFill($attributes);
        $model->setAttribute('organisation_id', $organisation->id->toString());
        $model->save();

        $this->idMap[$id] = $model;
        $this->written[$id] = true;
        $result->created++;

        if ($model instanceof Document) {
            $this->importMedia($model, $entity, $zipPath);
        }
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    private function suffixCopy(array $attributes, TransferEntityType $type): array
    {
        $matchColumn = $type->matchColumn();

        if ($matchColumn === null || !is_string($attributes[$matchColumn] ?? null)) {
            return $attributes;
        }

        $attributes[$matchColumn] = sprintf('%s%s', $attributes[$matchColumn], __('transfer.copy_suffix'));

        return $attributes;
    }

    /**
     * Owned entities (address, remarks, FG remark) are only written onto owners
     * that were created or overwritten by this import; untouched owners keep their own.
     *
     * @param array<string, mixed> $entity
     */
    private function importOwned(TransferEntityType $type, array $entity, User $user): void
    {
        $owner = $entity['owner'] ?? null;

        if (!is_array($owner) || !is_string($owner['id'] ?? null)) {
            return;
        }

        $ownerId = $owner['id'];
        $ownerModel = $this->idMap[$ownerId] ?? null;

        if ($ownerModel === null || !isset($this->written[$ownerId])) {
            return;
        }

        $attributes = $this->attributeRemapper->attributes($entity);
        $body = $attributes['body'] ?? '';

        $relationName = match ($type) {
            TransferEntityType::ADDRESS => 'address',
            TransferEntityType::FG_REMARK => 'fgRemark',
            TransferEntityType::REMARK => 'remarks',
            default => null,
        };

        if ($relationName === null) {
            return;
        }

        $relation = ModelGraph::relation($ownerModel, $relationName);

        if ($relation instanceof MorphMany) {
            $relation->firstOrCreate(['body' => $body], ['user_id' => $user->id->toString()]);

            return;
        }

        if (!$relation instanceof MorphOne) {
            return;
        }

        $relation->updateOrCreate([], $type === TransferEntityType::ADDRESS ? $attributes : ['body' => $body]);
    }

    /**
     * @param array<string, mixed> $entity
     */
    private function importMedia(Document $document, array $entity, string $zipPath): void
    {
        $mediaItems = $entity['media'] ?? [];

        if (!is_array($mediaItems)) {
            return;
        }

        foreach ($mediaItems as $mediaItem) {
            if (!is_array($mediaItem)) {
                continue;
            }

            $zipEntryPath = $mediaItem['zip_path'] ?? null;
            $fileName = $mediaItem['file_name'] ?? null;
            $collectionName = $mediaItem['collection_name'] ?? null;

            if (!is_string($zipEntryPath) || !is_string($fileName) || !is_string($collectionName)) {
                continue;
            }

            $contents = $this->bundleReader->readMedia($zipPath, $zipEntryPath);

            if ($contents === null) {
                continue;
            }

            $name = $mediaItem['name'] ?? null;

            $document->addMediaFromString($contents)
                ->usingFileName($fileName)
                ->usingName(is_string($name) ? $name : $fileName)
                ->toMediaCollection($collectionName);
        }
    }
}
