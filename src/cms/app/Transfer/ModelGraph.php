<?php

declare(strict_types=1);

namespace App\Transfer;

use App\Components\Uuid\UuidInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Webmozart\Assert\Assert;

use function array_values;
use function method_exists;

/**
 * Typed access to model identity and dynamically named relations for the transfer module.
 */
final class ModelGraph
{
    public static function id(Model $model): string
    {
        // not all models cast their id to a uuid object (e.g. LookupListModel casts to string)
        $key = $model->getAttribute($model->getKeyName());

        if ($key instanceof UuidInterface) {
            return $key->toString();
        }

        Assert::string($key);

        return $key;
    }

    /**
     * @return list<Model>
     */
    public static function related(Model $model, string $relationName): array
    {
        if (!method_exists($model, $relationName)) {
            return [];
        }

        $value = $model->getAttribute($relationName);

        if (!$value instanceof EloquentCollection) {
            return [];
        }

        return array_values($value->all());
    }

    public static function relatedOne(Model $model, string $relationName): ?Model
    {
        if (!method_exists($model, $relationName)) {
            return null;
        }

        $value = $model->getAttribute($relationName);

        return $value instanceof Model ? $value : null;
    }

    /**
     * @return Relation<Model, Model, mixed>|null
     */
    public static function relation(Model $model, string $relationName): ?Relation
    {
        if (!method_exists($model, $relationName)) {
            return null;
        }

        $relation = $model->{$relationName}(); // @phpstan-ignore method.dynamicName

        return $relation instanceof Relation ? $relation : null;
    }
}
