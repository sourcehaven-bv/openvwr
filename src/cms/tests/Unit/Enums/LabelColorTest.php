<?php

declare(strict_types=1);

use App\Enums\LabelColor;

it('picks the first colour when nothing is in use', function (): void {
    expect(LabelColor::leastUsed([]))
        ->toBe(LabelColor::cases()[0]);
});

it('picks a colour that is not in use yet', function (): void {
    $usage = [];

    foreach (LabelColor::cases() as $labelColor) {
        $usage[$labelColor->value] = 1;
    }

    unset($usage[LabelColor::VIOLET->value]);

    expect(LabelColor::leastUsed($usage))
        ->toBe(LabelColor::VIOLET);
});

it('picks the least used colour once every colour is taken', function (): void {
    $usage = [];

    foreach (LabelColor::cases() as $labelColor) {
        $usage[$labelColor->value] = 3;
    }

    $usage[LabelColor::TEAL->value] = 1;

    expect(LabelColor::leastUsed($usage))
        ->toBe(LabelColor::TEAL);
});

it('breaks ties in a stable order', function (): void {
    $usage = [
        LabelColor::AMBER->value => 2,
        LabelColor::OLIVE->value => 2,
    ];

    // Every other colour is unused and ties at zero; the enum's order decides,
    // so the outcome does not depend on how the counts came back from the
    // database.
    expect(LabelColor::leastUsed($usage))
        ->toBe(LabelColor::MOSS)
        ->and(LabelColor::leastUsed($usage))
        ->toBe(LabelColor::MOSS);
});

it('ignores values that are not part of the palette', function (): void {
    $usage = ['chartreuse' => 99];

    expect(LabelColor::leastUsed($usage))
        ->toBe(LabelColor::cases()[0]);
});
