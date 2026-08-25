<?php

declare(strict_types=1);

namespace App\Filament\Resources\TagResource;

use App\Enums\LabelColor;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

use function __;
use function e;

class TagResourceForm
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getSchema());
    }

    /**
     * @return array<Component>
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
     * The swatch uses the same CSS variable Filament publishes for the
     * registered palette (see LabelColorPalette), so it cannot drift from the
     * colour the badge will actually use.
     */
    private static function swatch(LabelColor $labelColor): string
    {
        return
            '<span class="flex items-center gap-x-2">'
            . '<span class="h-4 w-4 rounded-full" style="background-color: rgb(var(--'
            . e($labelColor->value)
            . '-600))"></span>'
            . '<span>' . e($labelColor->label()) . '</span>'
            . '</span>';
    }
}
