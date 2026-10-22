<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\RepeatableEntry;

use function __;

class RemarksEntry extends RepeatableEntry
{
    public static function make(?string $name = 'remarks'): static
    {
        return parent::make($name)
            ->label(__('remark.model_plural'))
            ->placeholder(__('general.none_selected'))
            ->schema([
                TextareaEntry::make('body')
                    ->label(__('remark.body')),
            ]);
    }
}
