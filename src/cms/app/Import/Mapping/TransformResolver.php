<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\MappingTransform;
use App\Models\Casts\CalendarDateCast;
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
    public function __construct(
        private readonly TableColumns $tableColumns = new TableColumns(),
    ) {
    }

    public function forAttribute(Model $model, string $attribute): MappingTransform
    {
        $cast = $model->getCasts()[$attribute] ?? null;

        return match ($cast) {
            'date', 'datetime', 'immutable_date', 'immutable_datetime', CalendarDateCast::class => MappingTransform::Date,
            'bool', 'boolean' => MappingTransform::Boolean,
            'int', 'integer' => MappingTransform::Integer,
            'array', 'json', 'collection' => MappingTransform::StringList,
            null => $this->fromColumnType($model, $attribute),
            default => MappingTransform::Text,
        };
    }

    /**
     * Without a cast the column itself says what it holds. A model that
     * forgot to cast a yes/no column would otherwise take "ja" as text and
     * have the database refuse it.
     */
    private function fromColumnType(Model $model, string $attribute): MappingTransform
    {
        return match ($this->tableColumns->type($model, $attribute)) {
            'bool' => MappingTransform::Boolean,
            'date', 'timestamp', 'timestamptz' => MappingTransform::Date,
            'int2', 'int4', 'int8' => MappingTransform::Integer,
            'json', 'jsonb' => MappingTransform::StringList,
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
