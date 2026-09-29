<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Models\Tag;
use Filament\Infolists\Components\TextEntry;

use function __;

/**
 * The labels on a processing record, each in its own colour.
 *
 * A badge rather than the bulleted list SelectMultipleEntry renders, because
 * the colour needs a surface to sit on. The name is part of the badge: the
 * colour is there to make a label recognisable at a glance, not to identify it
 * on its own.
 *
 * Separate from SelectMultipleEntry because that component is shared by more
 * than twenty other relations, none of which have a colour.
 *
 * The entry points at the tags relation rather than at tags.name, so each item
 * of the state is the Tag itself. Both the name and the colour then come from
 * the same record; keying on the name would have to be matched back to a
 * colour, which breaks as soon as two labels share one.
 */
class TagsEntry extends TextEntry
{
    public static function make(?string $name = 'tags'): static
    {
        return parent::make($name)
            ->label(__('tag.model_plural'))
            ->badge()
            ->formatStateUsing(static fn (mixed $state): string => $state instanceof Tag
                ? $state->name
                : '')
            ->color(static fn (mixed $state): string => $state instanceof Tag
                ? ($state->color->value ?? 'gray')
                : 'gray')
            ->placeholder(__('general.none_selected'));
    }
}
