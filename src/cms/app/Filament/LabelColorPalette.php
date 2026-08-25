<?php

declare(strict_types=1);

namespace App\Filament;

use App\Enums\LabelColor;

/**
 * The shade ramps for the label colours.
 *
 * Filament expects a full 50..950 ramp per colour, in "r, g, b" form, not a
 * single hex value; it picks shades from the ramp for the badge background,
 * text and dark-mode variants.
 *
 * These values are generated from OKLCH coordinates -- see LabelColor for why
 * that colour space and not HSL -- and then written out here as constants.
 * They are not computed at runtime: the palette is fixed, and a literal table
 * is easier to inspect and diff than a conversion routine. The generator and
 * the contrast guarantee are pinned by LabelColorContrastTest, which recomputes
 * the ratios from these very values.
 *
 * The hue of each colour is noted so the spacing stays visible: they sit at
 * least 22 degrees apart, and at least 25 degrees away from the house style
 * (7) and the semantic colours in StateColor (0, 32, 142, 221).
 */
class LabelColorPalette
{
    /**
     * @return array<string, array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string}>
     */
    public static function all(): array
    {
        return [
            LabelColor::AMBER->value => self::AMBER,
            LabelColor::OLIVE->value => self::OLIVE,
            LabelColor::MOSS->value => self::MOSS,
            LabelColor::EMERALD->value => self::EMERALD,
            LabelColor::TEAL->value => self::TEAL,
            LabelColor::AZURE->value => self::AZURE,
            LabelColor::INDIGO->value => self::INDIGO,
            LabelColor::VIOLET->value => self::VIOLET,
            LabelColor::PURPLE->value => self::PURPLE,
            LabelColor::MAGENTA->value => self::MAGENTA,
        ];
    }

    /** hue 57 */
    private const AMBER = [
        50 => '255, 241, 229',
        100 => '255, 228, 207',
        200 => '255, 208, 174',
        300 => '247, 184, 138',
        400 => '237, 165, 111',
        500 => '198, 117, 49',
        600 => '152, 75, 0',
        700 => '128, 62, 0',
        800 => '104, 49, 0',
        900 => '83, 40, 0',
        950 => '63, 32, 6',
    ];

    /** hue 79 */
    private const OLIVE = [
        50 => '254, 244, 227',
        100 => '252, 232, 202',
        200 => '244, 214, 167',
        300 => '233, 192, 127',
        400 => '221, 175, 97',
        500 => '182, 129, 13',
        600 => '138, 87, 0',
        700 => '116, 72, 0',
        800 => '94, 58, 0',
        900 => '75, 47, 0',
        950 => '56, 37, 0',
    ];

    /** hue 101 */
    private const MOSS = [
        50 => '248, 246, 227',
        100 => '241, 237, 203',
        200 => '228, 221, 167',
        300 => '212, 202, 128',
        400 => '198, 186, 98',
        500 => '158, 142, 15',
        600 => '116, 100, 0',
        700 => '97, 83, 0',
        800 => '78, 67, 0',
        900 => '63, 54, 0',
        950 => '47, 41, 0',
    ];

    /** hue 167 */
    private const EMERALD = [
        50 => '230, 251, 242',
        100 => '208, 245, 229',
        200 => '174, 234, 209',
        300 => '136, 219, 186',
        400 => '105, 206, 169',
        500 => '7, 165, 123',
        600 => '0, 121, 83',
        700 => '0, 102, 69',
        800 => '0, 82, 55',
        900 => '0, 66, 44',
        950 => '0, 50, 34',
    ];

    /** hue 189 */
    private const TEAL = [
        50 => '227, 251, 248',
        100 => '202, 246, 241',
        200 => '165, 234, 228',
        300 => '120, 219, 212',
        400 => '82, 206, 198',
        500 => '0, 164, 156',
        600 => '0, 121, 114',
        700 => '0, 101, 95',
        800 => '0, 82, 77',
        900 => '0, 66, 62',
        950 => '0, 50, 47',
    ];

    /** hue 246 */
    private const AZURE = [
        50 => '232, 247, 255',
        100 => '211, 239, 255',
        200 => '181, 224, 255',
        300 => '147, 206, 255',
        400 => '122, 190, 250',
        500 => '62, 146, 214',
        600 => '0, 104, 168',
        700 => '0, 86, 141',
        800 => '0, 69, 116',
        900 => '6, 56, 92',
        950 => '10, 43, 68',
    ];

    /** hue 268 */
    private const INDIGO = [
        50 => '238, 245, 255',
        100 => '222, 235, 255',
        200 => '199, 218, 255',
        300 => '173, 198, 255',
        400 => '153, 181, 254',
        500 => '106, 136, 219',
        600 => '68, 93, 173',
        700 => '55, 77, 145',
        800 => '44, 62, 119',
        900 => '36, 50, 94',
        950 => '28, 39, 70',
    ];

    /** hue 290 */
    private const VIOLET = [
        50 => '244, 243, 255',
        100 => '234, 231, 255',
        200 => '217, 212, 255',
        300 => '198, 189, 255',
        400 => '182, 171, 249',
        500 => '140, 125, 213',
        600 => '100, 83, 167',
        700 => '83, 69, 141',
        800 => '67, 55, 115',
        900 => '53, 45, 91',
        950 => '41, 35, 68',
    ];

    /** hue 312 */
    private const PURPLE = [
        50 => '251, 241, 255',
        100 => '245, 227, 255',
        200 => '234, 206, 253',
        300 => '220, 182, 244',
        400 => '207, 163, 235',
        500 => '166, 115, 197',
        600 => '124, 74, 152',
        700 => '103, 61, 128',
        800 => '84, 48, 104',
        900 => '67, 40, 83',
        950 => '51, 31, 62',
    ];

    /** hue 334 */
    private const MAGENTA = [
        50 => '255, 239, 252',
        100 => '255, 225, 248',
        200 => '248, 202, 238',
        300 => '238, 176, 225',
        400 => '227, 157, 212',
        500 => '187, 108, 173',
        600 => '142, 67, 129',
        700 => '119, 54, 108',
        800 => '97, 43, 88',
        900 => '77, 36, 70',
        950 => '58, 29, 53',
    ];
}
