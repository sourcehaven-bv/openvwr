<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

use function __;

class ImportNumberColumn extends TextColumn
{
    public static function make(?string $name = 'import_number'): static
    {
        return parent::make($name)
            ->label(__('processing_record.import_number'))
            ->searchable()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
