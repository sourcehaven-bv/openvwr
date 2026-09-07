<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\MappingTransform;
use Illuminate\Database\Eloquent\Model;

/**
 * Derives how a value must be converted from the target attribute itself.
 *
 * The model's cast is the authority: mapping a column onto a date field can
 * only ever mean "read this as a date", so the conversion is not something the
 * user should have to pick (and get wrong).
 */
class TransformResolver
{
    public function forAttribute(Model $model, string $attribute): MappingTransform
    {
        $cast = $model->getCasts()[$attribute] ?? null;

        return match ($cast) {
            'date', 'datetime', 'immutable_date', 'immutable_datetime' => MappingTransform::Date,
            'bool', 'boolean' => MappingTransform::Boolean,
            'int', 'integer' => MappingTransform::Integer,
            'array', 'json', 'collection' => MappingTransform::StringList,
            default => MappingTransform::Text,
        };
    }

    /**
     * @param class-string<Model> $modelClass
     *
     * @return array<string, MappingTransform>
     */
    public function forModel(string $modelClass): array
    {
        $model = new $modelClass();

        $transforms = [];
        foreach ($model->getFillable() as $attribute) {
            $transforms[$attribute] = $this->forAttribute($model, $attribute);
        }

        return $transforms;
    }
}
