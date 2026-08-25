<?php

declare(strict_types=1);

namespace App\Filament;

use App\Enums\LabelColor;
use Illuminate\Support\HtmlString;

use function e;

/**
 * A label rendered as its name preceded by a dot in its own colour.
 *
 * Used wherever a label appears in a Select rather than as a badge: the label
 * picker on a processing record, and the label filter above a table. Filament
 * renders those options as plain text in the primary colour, which would leave
 * the label unrecognisable next to the coloured badge for the same label
 * elsewhere on the page.
 *
 * The name is always part of the output. The dot is added to it, never instead
 * of it (WCAG 1.4.1).
 *
 * The colour comes from the CSS variable Filament publishes for the registered
 * palette (see LabelColorPalette), so it cannot drift from the badge's colour.
 * A label without a colour gets no dot at all rather than a grey one, which
 * would read as a colour in its own right.
 */
class LabelSwatch
{
    public static function make(?LabelColor $labelColor, string $name): HtmlString
    {
        if (!$labelColor instanceof LabelColor) {
            return new HtmlString('<span>' . e($name) . '</span>');
        }

        return new HtmlString(
            '<span class="fi-label-swatch">'
            . '<span class="fi-label-swatch__dot" style="background-color: rgb(var(--'
            . e($labelColor->value)
            . '-600))"></span>'
            . '<span>' . e($name) . '</span>'
            . '</span>',
        );
    }
}
