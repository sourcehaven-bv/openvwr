<?php

declare(strict_types=1);

namespace App\Collections\Dpia;

use App\Enums\Dpia\RiskLevel;
use App\Models\Dpia\DpiaRisk;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends Collection<array-key, DpiaRisk>
 */
class DpiaRiskCollection extends Collection
{
    public function highRisks(): self
    {
        return $this->filter(
            static fn (DpiaRisk $risk): bool => $risk->level === RiskLevel::HIGH,
        );
    }
}
