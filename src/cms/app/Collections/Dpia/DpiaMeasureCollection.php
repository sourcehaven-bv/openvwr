<?php

declare(strict_types=1);

namespace App\Collections\Dpia;

use App\Enums\Dpia\RiskLevel;
use App\Models\Dpia\DpiaMeasure;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends Collection<array-key, DpiaMeasure>
 */
class DpiaMeasureCollection extends Collection
{
    /**
     * Measures that leave a high residual risk. If any exist, the AP has to be
     * consulted before the processing starts (artikel 36 AVG).
     */
    public function withHighResidualRisk(): self
    {
        return $this->filter(
            static fn (DpiaMeasure $measure): bool => $measure->residual_level === RiskLevel::HIGH,
        );
    }
}
