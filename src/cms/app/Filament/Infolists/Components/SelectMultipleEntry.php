<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;

use function __;

class SelectMultipleEntry extends TextEntry
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->listWithLineBreaks()
            ->bulleted()
            ->placeholder(__('general.none_selected'));
    }
}
