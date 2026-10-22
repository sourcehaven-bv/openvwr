<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\DatePicker;

use App\Services\DateFormatService;
use Filament\Forms\Components\DatePicker as FilamentDatePicker;

class DatePicker extends FilamentDatePicker
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->displayFormat(DateFormatService::FORMAT_DATE)
            ->native(false);
    }
}
