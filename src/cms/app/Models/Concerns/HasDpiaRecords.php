<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Contracts\Cloneable;
use App\Models\Dpia\DpiaRecord;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Links a record (an AVG or Wpg verwerking, a system, ...) to the DPIAs that
 * cover it.
 *
 * Many-to-many on purpose: one DPIA may cover a series of comparable
 * processing operations (artikel 35, eerste lid, AVG and overweging 92), and a
 * single processing operation may be touched by more than one DPIA over time.
 */
trait HasDpiaRecords
{
    final public function initializeHasDpiaRecords(): void
    {
        if ($this instanceof Cloneable) {
            $this->addCloneableRelations(['dpiaRecords']);
        }
    }

    /**
     * @return MorphToMany<DpiaRecord, $this>
     */
    final public function dpiaRecords(): MorphToMany
    {
        return $this->morphToMany(DpiaRecord::class, 'dpia_record_relatable');
    }
}
