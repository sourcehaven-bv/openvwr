<?php

declare(strict_types=1);

namespace App\Enums\Dpia;

use App\Enums\StateColor;

use function __;

/**
 * What the pre-scan concluded for a single assessment type. "Verplicht" and
 * "aanbevolen" are kept apart on purpose: the Rijksmodel treats a recommended
 * DPIA as a real option ("kan het waardevol zijn om toch een DPIA uit te
 * voeren"), and only a mandatory one is a compliance failure when skipped.
 */
enum PrescanOutcome: string
{
    case REQUIRED = 'verplicht';
    case RECOMMENDED = 'aanbevolen';
    case NOT_REQUIRED = 'niet_verplicht';

    public function label(): string
    {
        return __('dpia_prescan_record.outcome_' . $this->value);
    }

    public function color(): StateColor
    {
        return match ($this) {
            self::REQUIRED => StateColor::DANGER,
            self::RECOMMENDED => StateColor::WARNING,
            self::NOT_REQUIRED => StateColor::SUCCESS,
        };
    }
}
