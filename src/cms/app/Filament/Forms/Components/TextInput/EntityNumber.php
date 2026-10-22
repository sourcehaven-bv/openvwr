<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\TextInput;

use App\Models\Contracts\EntityNumerable;
use Filament\Forms\Components\TextInput;

use function __;

class EntityNumber extends TextInput
{
    public static function make(?string $name = 'entityNumber.number'): static
    {
        return parent::make('entityNumber.number')
            ->label(__('processing_record.number'))
            ->formatStateUsing(static function (?EntityNumerable $record): ?string {
                return $record?->getNumber();
            })
            ->visibleOn('edit')
            ->disabled();
    }
}
