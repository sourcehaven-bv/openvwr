<?php

declare(strict_types=1);

namespace App\Collections\Dpia;

use App\Models\Dpia\DpiaPersonalData;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends Collection<array-key, DpiaPersonalData>
 */
class DpiaPersonalDataCollection extends Collection
{
    /**
     * The gegevens whose verwerking is in principle forbidden and therefore
     * needs a ground in paragraaf 12.
     */
    public function requiringExceptionGround(): self
    {
        return $this->filter(
            static fn (DpiaPersonalData $personalData): bool =>
                $personalData->type?->requiresExceptionGround() === true,
        );
    }

    public function missingExceptionGround(): self
    {
        return $this->filter(
            static fn (DpiaPersonalData $personalData): bool => $personalData->missesExceptionGround(),
        );
    }
}
