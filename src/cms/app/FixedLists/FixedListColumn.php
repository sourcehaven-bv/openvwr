<?php

declare(strict_types=1);

namespace App\FixedLists;

use Illuminate\Database\Eloquent\Model;

use function in_array;

/**
 * A database column whose values are supposed to come from a fixed list.
 *
 * @template TModel of Model
 */
class FixedListColumn
{
    /**
     * @param class-string<TModel> $model
     * @param list<string> $ignoredValues Values the column may legitimately hold without being list members,
     *                                    such as a sentinel that routes the answer to a free text field.
     */
    public function __construct(
        public readonly string $model,
        public readonly string $column,
        public readonly FixedList $list,
        public readonly array $ignoredValues = [],
    ) {
    }

    public function ignores(string $value): bool
    {
        return in_array($value, $this->ignoredValues, true);
    }
}
