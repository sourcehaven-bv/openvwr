<?php

declare(strict_types=1);

namespace Tests\Helpers;

use function array_map;
use function explode;
use function max;
use function min;
use function trim;

/**
 * The WCAG 2.1 contrast formula, for asserting that a colour pair is readable.
 *
 * Implemented here rather than pulled from the palette definition on purpose:
 * a test that recomputes the ratio from the registered shades will catch an
 * edit to those shades, where a test that reused the generator would not.
 */
class ContrastTestHelper
{
    public const AA_NORMAL_TEXT = 4.5;

    /**
     * @param string $foreground "r, g, b" as registered with Filament
     * @param string $background "r, g, b" as registered with Filament
     */
    public static function ratio(string $foreground, string $background): float
    {
        $lighter = max(self::relativeLuminance($foreground), self::relativeLuminance($background));
        $darker = min(self::relativeLuminance($foreground), self::relativeLuminance($background));

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * WCAG 2.1 relative luminance: sRGB channels linearised, then weighted for
     * the eye's differing sensitivity to red, green and blue.
     */
    private static function relativeLuminance(string $rgb): float
    {
        $channels = array_map(
            static function (string $value): float {
                $channel = (float) trim($value) / 255;

                return $channel <= 0.039_28
                    ? $channel / 12.92
                    : (($channel + 0.055) / 1.055) ** 2.4;
            },
            explode(',', $rgb),
        );

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }
}
