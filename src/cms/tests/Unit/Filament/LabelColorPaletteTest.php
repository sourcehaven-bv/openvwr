<?php

declare(strict_types=1);

use App\Enums\LabelColor;
use App\Filament\LabelColorPalette;
use Tests\Helpers\ContrastTestHelper;

// The palette is only worth having if it stays readable, so the contrast is
// asserted rather than assumed. The ratios are recomputed from the registered
// shades themselves, which means editing a value in LabelColorPalette without
// checking it fails here instead of in production.
//
// The pairs mirror how Filament renders a badge: the 600 shade as text on the
// 50 shade in light mode, and the 400 shade on the 950 shade in dark mode.
it('registers a ramp for every label colour', function (): void {
    $palette = LabelColorPalette::all();

    expect($palette)->toHaveCount(count(LabelColor::cases()));

    foreach (LabelColor::cases() as $labelColor) {
        expect($palette)->toHaveKey($labelColor->value);
        expect($palette[$labelColor->value])
            ->toHaveKeys([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950]);
    }
});

it('meets WCAG AA in light mode', function (LabelColor $labelColor): void {
    $shades = LabelColorPalette::all()[$labelColor->value];

    expect(ContrastTestHelper::ratio($shades[600], $shades[50]))
        ->toBeGreaterThanOrEqual(ContrastTestHelper::AA_NORMAL_TEXT);
})->with(LabelColor::cases());

it('meets WCAG AA in dark mode', function (LabelColor $labelColor): void {
    $shades = LabelColorPalette::all()[$labelColor->value];

    expect(ContrastTestHelper::ratio($shades[400], $shades[950]))
        ->toBeGreaterThanOrEqual(ContrastTestHelper::AA_NORMAL_TEXT);
})->with(LabelColor::cases());
