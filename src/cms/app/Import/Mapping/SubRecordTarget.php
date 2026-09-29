<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Webmozart\Assert\Assert;

/**
 * A record that belongs to exactly one other record and is spread over several
 * source columns, such as the address of a processor.
 *
 * Unlike a relation this is never shared or looked up: it is created with the
 * record it belongs to, from whichever columns the source happens to provide.
 */
readonly class SubRecordTarget
{
    /**
     * @param class-string<Model> $modelClass
     * @param array<string, string> $attributes attribute name => label key
     * @param Closure $resolver returns the MorphOne relation for a record
     */
    public function __construct(
        public string $key,
        public string $modelClass,
        public array $attributes,
        public string $labelKey,
        private Closure $resolver,
    ) {
    }

    /**
     * @return MorphOne<Model, Model>
     */
    public function relationFor(Model $model): MorphOne
    {
        $relation = ($this->resolver)($model);
        Assert::isInstanceOf($relation, MorphOne::class);

        return $relation;
    }
}
