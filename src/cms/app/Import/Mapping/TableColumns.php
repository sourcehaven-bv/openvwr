<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Webmozart\Assert\Assert;

use function array_key_exists;

/**
 * The columns a model's table really has, with their types, read once per
 * table. A model's fillable list and casts are what the developer wrote; the
 * table is what the database will accept.
 */
class TableColumns
{
    /** @var array<string, array<string, string>> table => column => type */
    private array $tables = [];

    /**
     * @return array<string, string> column name => type name
     */
    public function types(Model $model): array
    {
        $table = $model->getTable();

        if (!array_key_exists($table, $this->tables)) {
            $types = [];
            foreach (Schema::getColumns($table) as $column) {
                Assert::isArray($column);
                Assert::string($column['name']);
                Assert::string($column['type_name']);
                $types[$column['name']] = $column['type_name'];
            }

            $this->tables[$table] = $types;
        }

        return $this->tables[$table];
    }

    public function type(Model $model, string $attribute): ?string
    {
        return $this->types($model)[$attribute] ?? null;
    }
}
