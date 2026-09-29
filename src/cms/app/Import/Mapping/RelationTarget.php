<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\MissingEntityPolicy;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Webmozart\Assert\Assert;

/**
 * A relation a source column can feed: which model it resolves to, which
 * attribute holds its name, and how a row attaches to it.
 *
 * The shared entities do not agree on a name attribute -- Processor and
 * Responsible use "name" while System and Receiver use "description" -- so it is
 * declared here rather than guessed.
 */
readonly class RelationTarget
{
    /**
     * @param class-string<Model> $modelClass
     * @param Closure $resolver
     *        returns the relation for a record; a callable keeps the call
     *        type-checked instead of dynamic
     */
    public function __construct(
        public string $key,
        public string $modelClass,
        public string $nameAttribute,
        public string $labelKey,
        private Closure $resolver,
        /**
         * What happens to a name the register does not hold. Registers that are
         * maintained deliberately report it: a data breach naming an unknown
         * processing record should flag that, not silently add an empty one.
         */
        public MissingEntityPolicy $missing = MissingEntityPolicy::Create,
        /**
         * Extra attributes a source may fill on the related record, beyond the
         * name it is matched on. Keys are attribute names, values label keys.
         *
         * @var array<string, string>
         */
        public array $extraAttributes = [],
    ) {
    }

    /**
     * @return MorphToMany<Model, Model, covariant Pivot>
     */
    public function relationFor(Model $model): MorphToMany
    {
        $relation = ($this->resolver)($model);
        Assert::isInstanceOf($relation, MorphToMany::class);

        return $relation;
    }
}
