<?php

declare(strict_types=1);

use App\Enums\LabelColor;
use App\Models\Organisation;
use App\Models\Tag;

it('gives a new label a colour', function (): void {
    $tag = Tag::factory()->create(['color' => null]);

    expect($tag->color)->toBeInstanceOf(LabelColor::class);
});

it('keeps a colour that was chosen', function (): void {
    $tag = Tag::factory()->create(['color' => LabelColor::MAGENTA]);

    expect($tag->color)->toBe(LabelColor::MAGENTA);
});

it('spreads the palette over the labels of one organisation', function (): void {
    $organisation = Organisation::factory()->create();

    $colors = [];

    for ($i = 0; $i < count(LabelColor::cases()); $i++) {
        $tag = Tag::factory()->create([
            'organisation_id' => $organisation->id,
            'color' => null,
        ]);

        $colors[] = $tag->color;
    }

    // One label per colour: nothing repeats before the palette is used up.
    expect($colors)->toHaveCount(count(LabelColor::cases()))
        ->and(array_unique(array_map(
            static fn (LabelColor $labelColor): string => $labelColor->value,
            $colors,
        )))->toHaveCount(count(LabelColor::cases()));
});

it('starts over once every colour is taken', function (): void {
    $organisation = Organisation::factory()->create();

    for ($i = 0; $i < count(LabelColor::cases()); $i++) {
        Tag::factory()->create([
            'organisation_id' => $organisation->id,
            'color' => null,
        ]);
    }

    $tag = Tag::factory()->create([
        'organisation_id' => $organisation->id,
        'color' => null,
    ]);

    // Every colour is now used once, so the tie falls back to the enum order.
    expect($tag->color)->toBe(LabelColor::cases()[0]);
});

it('counts colours per organisation', function (): void {
    $first = Organisation::factory()->create();
    $second = Organisation::factory()->create();

    Tag::factory()->create([
        'organisation_id' => $first->id,
        'color' => null,
    ]);

    $tag = Tag::factory()->create([
        'organisation_id' => $second->id,
        'color' => null,
    ]);

    // The other organisation's label does not push this one along: each
    // organisation gets the full palette from the start.
    expect($tag->color)->toBe(LabelColor::cases()[0]);
});
