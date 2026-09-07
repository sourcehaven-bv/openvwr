<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function in_array;
use function is_string;

/**
 * What a record starts out with when the source says nothing: the values the
 * register's own form fills in before the user touches it. Only columns the
 * database insists on are considered; an optional field stays empty rather
 * than getting a made-up answer.
 */
class FormDefaults
{
    /**
     * Never treated as missing: filled in by the application, not the source.
     */
    private const GENERATED = ['id', 'number', 'organisation_id', 'import_id', 'created_at', 'updated_at', 'deleted_at'];

    /** @var array<class-string<Model>, array{required: array<int, string>, defaults: array<string, mixed>}> */
    private array $cache = [];

    public function __construct(
        private readonly FieldOptions $fieldOptions,
    ) {
    }

    /**
     * Required columns the form has no starting value for; the source must
     * supply these.
     *
     * @param class-string<Model> $modelClass
     *
     * @return array<int, string>
     */
    public function required(string $modelClass): array
    {
        return $this->inspect($modelClass)['required'];
    }

    /**
     * Required columns with the value the form starts them out with: "no" for
     * a yes/no field, the first option for a fixed choice.
     *
     * @param class-string<Model> $modelClass
     *
     * @return array<string, mixed>
     */
    public function defaults(string $modelClass): array
    {
        return $this->inspect($modelClass)['defaults'];
    }

    /**
     * @param class-string<Model> $modelClass
     *
     * @return array{required: array<int, string>, defaults: array<string, mixed>}
     */
    private function inspect(string $modelClass): array
    {
        if (array_key_exists($modelClass, $this->cache)) {
            return $this->cache[$modelClass];
        }

        $model = new $modelClass();
        $required = [];
        $defaults = [];

        foreach (Schema::getColumns($model->getTable()) as $column) {
            Assert::isArray($column);
            $name = $column['name'] ?? null;

            if (!is_string($name) || in_array($name, self::GENERATED, true)) {
                continue;
            }

            if (($column['nullable'] ?? true) === true || ($column['default'] ?? null) !== null) {
                continue;
            }

            if ($model->hasCast($name, ['bool', 'boolean'])) {
                $defaults[$name] = false;

                continue;
            }

            $option = $this->fieldOptions->default($model, $name);

            if ($option !== null) {
                $defaults[$name] = $option;

                continue;
            }

            $required[] = $name;
        }

        return $this->cache[$modelClass] = ['required' => $required, 'defaults' => $defaults];
    }
}
