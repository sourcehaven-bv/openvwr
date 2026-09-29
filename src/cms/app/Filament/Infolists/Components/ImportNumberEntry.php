<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;

use function __;

class ImportNumberEntry extends TextEntry
{
    public static function make(?string $name = 'import_number'): static
    {
        return parent::make($name)
            ->label(__('processing_record.import_number'));
    }
}
