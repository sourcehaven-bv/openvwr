<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\TextInput;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;

use function __;
use function filled;

class ImportNumber extends TextInput
{
    public static function make(string $name = 'import_number'): static
    {
        return parent::make($name)
            ->label(__('processing_record.import_number'))
            // Only meaningful when populated by imported content: hidden on
            // create, and on edit only shown when the field actually holds a
            // value, so records created in the CMS don't display an empty box.
            ->visible(static function (Get $get, string $operation) use ($name): bool {
                return $operation === 'edit' && filled($get($name));
            })
            ->disabled();
    }
}
