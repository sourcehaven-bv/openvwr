<?php

declare(strict_types=1);

use App\Enums\LabelColor;
use App\Filament\Infolists\Components\TagsEntry;
use App\Models\Tag;

// The entry keys on the tag itself rather than on its name, so that a label's
// colour and its name always come from the same record. These cover both
// callbacks, including the fallbacks for a state that is not a Tag.

it('renders a label as a badge in its own colour', function (): void {
    $tag = Tag::factory()->create(['color' => LabelColor::EMERALD]);

    $entry = TagsEntry::make();

    expect($entry->formatState($tag))->toBe($tag->name)
        ->and($entry->getColor($tag))->toBe(LabelColor::EMERALD->value);
});

it('falls back to grey for a label without a colour', function (): void {
    $tag = Tag::factory()->create();
    $tag->forceFill(['color' => null])->saveQuietly();
    $tag->refresh();

    expect(TagsEntry::make()->getColor($tag))->toBe('gray');
});

it('ignores a state that is not a label', function (): void {
    $entry = TagsEntry::make();

    expect($entry->formatState('not a tag'))->toBe('')
        ->and($entry->getColor('not a tag'))->toBe('gray');
});
