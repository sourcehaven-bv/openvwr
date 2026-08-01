<?php

declare(strict_types=1);

namespace App\Enums\Dpia;

use App\Enums\StateColor;

use function __;

/**
 * The three-point scale the Rijksmodel uses for kans (likelihood), impact and
 * the resulting risiconiveau.
 */
enum RiskLevel: string
{
    case LOW = 'laag';
    case MEDIUM = 'gemiddeld';
    case HIGH = 'hoog';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * Suggest a risiconiveau from kans x impact, following the matrix in
     * paragraaf 16 of the Rijksmodel: high only when both sides are at least
     * medium and one of them is high, low when either side is low.
     *
     * This is a suggestion, not a verdict. The model notes the matrix is
     * illustrative ("Deze risicomatrix is illustratief") and that a risk may be
     * scored lower when it cannot be mitigated further, so the invuller can
     * always override it with a motivation.
     */
    public static function suggest(?self $likelihood, ?self $impact): ?self
    {
        if (!$likelihood instanceof self || !$impact instanceof self) {
            return null;
        }

        if ($likelihood === self::LOW || $impact === self::LOW) {
            return self::LOW;
        }

        if ($likelihood === self::HIGH || $impact === self::HIGH) {
            return self::HIGH;
        }

        return self::MEDIUM;
    }

    public function label(): string
    {
        return __('dpia_record.risk_level_' . $this->value);
    }

    public function color(): StateColor
    {
        return match ($this) {
            self::LOW => StateColor::SUCCESS,
            self::MEDIUM => StateColor::WARNING,
            self::HIGH => StateColor::DANGER,
        };
    }
}
