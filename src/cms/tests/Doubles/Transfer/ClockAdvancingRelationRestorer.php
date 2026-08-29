<?php

declare(strict_types=1);

namespace Tests\Doubles\Transfer;

use App\Transfer\Import\RelationRestorer;
use App\Transfer\Import\TransferBundle;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * Advances the clock as the relations are written. The sync watermark and the pivot
 * timestamps are both whole-second columns, so the ordering bug they can expose only
 * surfaces when an import straddles a second boundary — occasionally in CI, almost
 * never locally. Forcing the crossing makes that deterministic.
 */
class ClockAdvancingRelationRestorer extends RelationRestorer
{
    public function __construct(private readonly RelationRestorer $inner)
    {
    }

    /**
     * @param array<string, Model> $idMap
     * @param array<string, true> $written
     */
    public function restore(TransferBundle $bundle, array $idMap, array $written): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now()->addSecond());

        $this->inner->restore($bundle, $idMap, $written);
    }
}
