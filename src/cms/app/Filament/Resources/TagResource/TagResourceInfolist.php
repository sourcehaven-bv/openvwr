<?php

declare(strict_types=1);

namespace App\Filament\Resources\TagResource;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Enums\LabelColor;
use App\Filament\LabelSwatch;
use App\Models\Tag;
use Filament\Infolists\Components\TextEntry;

use function __;

class TagResourceInfolist
{
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema(self::getSchema()),
            ]);
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
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
