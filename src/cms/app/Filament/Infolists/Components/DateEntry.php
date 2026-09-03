<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Services\DateFormatService;
use Filament\Infolists\Components\TextEntry;

class DateEntry extends TextEntry
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->dateTime(DateFormatService::FORMAT_DATE, DateFormatService::getDisplayTimezone());
    }
}
