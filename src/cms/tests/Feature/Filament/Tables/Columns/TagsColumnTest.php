<?php

declare(strict_types=1);

use App\Enums\LabelColor;
use App\Filament\Tables\Columns\TagsColumn;
use App\Models\Tag;

it('renders labels as coloured badges in an optional table column', function (): void {
    $tag = new Tag([
        'name' => 'Urgent',
        'color' => LabelColor::MAGENTA,
    ]);

    $column = TagsColumn::make();

    expect($column->getName())->toBe('tags')
        ->and($column->isBadge())->toBeTrue()
        ->and($column->isToggleable())->toBeTrue()
        ->and($column->isToggledHiddenByDefault())->toBeTrue()
        ->and($column->formatState($tag))->toBe('Urgent')
        ->and($column->getColor($tag))->toBe('magenta');
});

it('falls back safely when a label has no colour', function (): void {
    $tag = new Tag(['name' => 'Uncoloured']);

    expect(TagsColumn::make()->getColor($tag))->toBe('gray');
});
