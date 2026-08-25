<?php

declare(strict_types=1);

namespace App\Enums;

use function __;

/**
 * The colour palette for labels (tags).
 *
 * The hues are generated rather than picked by hand, so that they stay
 * coordinated with each other and with the colours already in the system. Two
 * constraints shaped the set:
 *
 * 1. A label must not read as a status. The house style sits on hue 7 and the
 *    semantic colours in StateColor occupy 0 (danger), 32 (warning), 142
 *    (success) and 221 (info), so every label hue keeps at least 25 degrees of
 *    distance from those, and 22 degrees from the other label hues. That
 *    leaves no red in the palette, which is a deliberate consequence of the
 *    house style owning that part of the colour wheel.
 *
 * 2. Every colour has to be readable. The shades are defined in OKLCH, which
 *    is perceptually uniform, so one fixed lightness reads equally across all
 *    hues. In HSL it does not: at a fixed HSL lightness the yellow in this set
 *    measured 2.0:1 against its own tint while the purple measured 5.78:1. The
 *    OKLCH shades registered in FilamentServiceProvider hold every colour
 *    between 4.87:1 and 5.80:1 in light mode, and above 7:1 in dark mode.
 *
 * The stored value is the name, not a hex code, so the palette can be adjusted
 * later without a data migration.
 *
 * Colour is supporting information only: it never carries meaning on its own.
 * The label name is always rendered alongside it (WCAG 1.4.1).
 */
enum LabelColor: string
{
    case AMBER = 'amber';
    case OLIVE = 'olive';
    case MOSS = 'moss';
    case EMERALD = 'emerald';
    case TEAL = 'teal';
    case AZURE = 'azure';
    case INDIGO = 'indigo';
    case VIOLET = 'violet';
    case PURPLE = 'purple';
    case MAGENTA = 'magenta';

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
     * Pick the colour for a new label from the colours already in use.
     *
     * The least-used colour wins, so the palette stays spread out instead of
     * one colour accumulating. Ties fall back to the order of the enum, which
     * keeps the outcome deterministic: the same input always yields the same
     * colour, so the backfill migration and the tests do not depend on
     * insertion order.
     *
     * @param array<int|string, int> $usageByValue colour value => times used
     */
    public static function leastUsed(array $usageByValue): self
    {
        $leastUsed = self::cases()[0];
        $lowest = $usageByValue[$leastUsed->value] ?? 0;

        foreach (self::cases() as $case) {
            $count = $usageByValue[$case->value] ?? 0;

            // Strictly lower, so the first case at a given count keeps the
            // spot: ties fall back to the order of the enum, which makes the
            // outcome independent of how the counts came back from the
            // database.
            if ($count >= $lowest) {
                continue;
            }

            $leastUsed = $case;
            $lowest = $count;
        }

        return $leastUsed;
    }

    public function label(): string
    {
        return __('tag.color_' . $this->value);
    }
}
