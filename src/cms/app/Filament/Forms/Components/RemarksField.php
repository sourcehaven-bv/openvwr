<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;

use function __;

class RemarksField extends Repeater
{
    public static function make(?string $name = 'remarks'): static
    {
        return parent::make($name)
            ->schema([
                Textarea::make('body')
                    ->label(__('remark.body'))
                    ->columnSpanFull(),
            ])
            ->label(__('remark.model_plural'))
            ->defaultItems(0)
            ->addActionLabel(__('remark.add'))
            ->relationship('remarks')
            ->reorderable(false);
    }
}
