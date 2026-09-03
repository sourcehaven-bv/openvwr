<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use App\Models\Tag;
use Filament\Tables\Columns\TextColumn;

use function __;

class TagsColumn extends TextColumn
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
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
