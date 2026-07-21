<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Bkwld\Cloner\Cloneable;
use Illuminate\Database\Eloquent\Model;

use function array_merge;
use function array_unique;
use function array_values;

trait IsCloneable
{
    use Cloneable;

    /** @var array<string> $cloneable_relations */
    protected array $cloneable_relations = [];

    /** @var array<string> $clone_exempt_attributes */
    protected array $clone_exempt_attributes = [
        'entity_number_id',
        'import_id',
        'origin_id',
    ];

    /**
     * @param array<string> $cloneableRelations
     */
    final public function addCloneableRelations(array $cloneableRelations): static
    {
        $this->cloneable_relations = array_values(array_unique(array_merge($this->cloneable_relations, $cloneableRelations)));

        return $this;
    }

    /**
     * @param array<string> $except
     */
    final public function clone(array $except = []): Model
    {
        $this->clone_exempt_attributes = array_values(array_unique(array_merge($this->clone_exempt_attributes, $except)));

        return $this->duplicate();
    }
}
