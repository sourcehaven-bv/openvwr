<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;

use function __;

class ParentSelectEntry extends TextEntry
{
    public static function make(?string $name = 'parent.name'): static
    {
        return parent::make($name)
            ->label(__('general.parent'))
            ->placeholder(__('general.none_selected'));
    }
}
