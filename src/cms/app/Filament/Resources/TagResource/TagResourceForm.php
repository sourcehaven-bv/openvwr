<?php

declare(strict_types=1);

namespace App\Filament\Resources\TagResource;

use Filament\Schemas\Schema;
use App\Enums\LabelColor;
use App\Filament\LabelSwatch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use function __;

class TagResourceForm
{
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(self::getSchema());
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('tag.name'))
                ->required(),
            self::color(),
        ];
    }

    /**
     * The colour is filled in automatically when a label is created (see
     * TagObserver), so this field is an override rather than a required
     * choice. Leaving it empty on an existing label is still possible, and
     * renders as grey.
     *
     * Each option carries its own swatch, so the choice is made on the colour
     * itself rather than on the name of the colour. The name stays next to it:
     * the swatch alone would leave the field unusable for anyone who cannot
     * distinguish the hues.
     */
    private static function color(): Select
    {
        $options = [];

        foreach (LabelColor::cases() as $labelColor) {
            $options[$labelColor->value] = self::swatch($labelColor);
        }

        return Select::make('color')
            ->label(__('tag.color'))
            ->options($options)
            ->allowHtml()
            ->native(false)
            ->selectablePlaceholder(false);
    }

    /**
     * The swatch is shared with the label picker and the label filter, so the
     * same label looks the same wherever it is chosen.
     */
    private static function swatch(LabelColor $labelColor): string
    {
        return LabelSwatch::make($labelColor, $labelColor->label())->toHtml();
    }
}
