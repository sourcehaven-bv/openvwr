<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

use function __;

class EntityNumber extends TextColumn
{
    public static function make(?string $name = 'entityNumber.number'): static
    {
        return parent::make($name)
            ->label(__('processing_record.number'))
            ->searchable()
            ->sortable();
    }
}
