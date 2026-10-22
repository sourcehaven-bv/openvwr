<?php

declare(strict_types=1);

namespace App\Filament;

use Filament\Support\Colors\Color;
use Filament\Support\View\Components\ButtonComponent;
use Filament\Support\View\Components\ColorMaps\ButtonComponentColorMap;
use Override;

/**
 * Keeps white text on filled buttons whose 600 shade is too light for it.
 *
 * Filament wants white text on shade 600. When that misses WCAG AA it falls
 * back to shade 400 with dark text, which turned every primary button pale
 * salmon: white on primary-600 is 4.15:1. This adds shade 700 as a step in
 * between, so such buttons get a slightly darker red that does pass AA (see
 * docs/accessibility.md). Palettes that pass on 600 are unaffected; pale ones
 * such as yellow still reach the 400 fallback.
 */
class AccessibleButtonComponent extends ButtonComponent
{
    /**
     * @param array<int, string> $color
     *
     * @return array<string, int>
     */
    #[Override]
    public function getColorMap(array $color): array
    {
        if ($this->isOutlined) {
            return parent::getColorMap($color);
        }

        return ButtonComponentColorMap::make($color)
            ->minContrastRatio(Color::WCAG_AA_TEXT)
            ->lightBackground(bg: 600, hover: 500)
            ->lightBackground(bg: 700, hover: 800)
            ->lightBackground(bg: 400, hover: 300, alternateHover: 500)
            ->darkBackground(bg: 600, hover: 500, alternateHover: 700)
            ->get();
    }
}
