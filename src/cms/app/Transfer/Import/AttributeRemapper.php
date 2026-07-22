<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Transfer\ModelGraph;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function array_keys;
use function is_string;

class AttributeRemapper
{
    /**
     * Replace every foreign key with its destination counterpart; keys pointing outside
     * the imported set are cleared so no identifiers from the source organisation leak in.
     * Parent links are restored separately, after all records exist.
     *
     * @param array<string, mixed> $entity
     * @param array<string, Model> $idMap destination model per bundle uuid
     *
     * @return array<string, mixed>
     */
    public function remap(array $entity, array $idMap): array
    {
        $attributes = $this->attributes($entity);

        foreach ($attributes as $column => $value) {
            if ($value === null || !Str::endsWith($column, '_id')) {
                continue;
            }

            $mapped = $column === 'parent_id' || !is_string($value)
                ? null
                : ($idMap[$value] ?? null);

            $attributes[$column] = $mapped === null ? null : ModelGraph::id($mapped);
        }

        return $attributes;
    }

    /**
     * @param array<string, mixed> $entity
     *
     * @return array<string, mixed>
     */
    public function attributes(array $entity): array
    {
        $attributes = $entity['attributes'] ?? [];
        Assert::isArray($attributes);
        Assert::allString(array_keys($attributes));

        /** @var array<string, mixed> $entityAttributes */
        $entityAttributes = $attributes;

        return $entityAttributes;
    }
}
