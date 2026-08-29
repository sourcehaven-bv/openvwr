<?php

declare(strict_types=1);

namespace App\Filament\Resources\TagResource;

use App\Enums\LabelColor;
use App\Filament\LabelSwatch;
use App\Models\Tag;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

use function __;

class TagResourceInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                Section::make()
                    ->schema(self::getSchema()),
            ]);
    }

    /**
     * @return array<Component>
     */
    public static function getSchema(): array
    {
        return [
            TextEntry::make('name')
                ->label(__('tag.name'))
                ->badge()
                ->color(static fn (Tag $tag): string => $tag->color->value ?? 'gray'),
            // The swatch, not just the colour's name: the point of the field is
            // which colour the label has.
            TextEntry::make('color')
                ->label(__('tag.color'))
                ->html()
                ->formatStateUsing(static fn (?LabelColor $state): string => $state instanceof LabelColor
                    ? LabelSwatch::make($state, $state->label())->toHtml()
                    : ''),
        ];
    }
}
