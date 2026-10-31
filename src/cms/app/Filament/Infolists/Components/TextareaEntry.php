<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;

use function nl2br;

class TextareaEntry extends TextEntry
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->html()
            ->formatStateUsing(static function (string $state) {
                return nl2br($state);
            });
    }
}
