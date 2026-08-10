<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Attributes\RelatedSnapshotSource;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Contracts\Cloneable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasAlgorithmRecords
{
    final public function initializeHasAlgorithmRecords(): void
    {
        if ($this instanceof Cloneable) {
            $this->addCloneableRelations(['algorithmRecords']);
        }
    }

    /**
     * @return MorphToMany<AlgorithmRecord, $this>
     */
    #[RelatedSnapshotSource(AlgorithmRecord::class)]
    final public function algorithmRecords(): MorphToMany
    {
        return $this->morphToMany(AlgorithmRecord::class, 'algorithm_record_relatable')->withTimestamps();
    }
}
