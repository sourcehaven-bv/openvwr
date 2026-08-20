<?php

declare(strict_types=1);

namespace App\FixedLists;

use Illuminate\Database\Eloquent\Model;

/**
 * A database column whose values are supposed to come from a fixed list.
 *
 * @template TModel of Model
 */
class FixedListColumn
{
    /**
     * @param class-string<TModel> $model
     */
    public function __construct(
        public readonly string $model,
        public readonly string $column,
        public readonly FixedList $list,
    ) {
    }
}
