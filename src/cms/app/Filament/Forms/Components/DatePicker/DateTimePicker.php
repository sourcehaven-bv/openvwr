<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\DatePicker;

use App\Config\Config;
use App\Services\DateFormatService;
use Filament\Forms\Components\DateTimePicker as FilamentDateTimePicker;

class DateTimePicker extends FilamentDateTimePicker
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->displayFormat(DateFormatService::FORMAT_DATE_TIME)
            ->timezone(Config::string('app.display_timezone'))
            ->native(false)
            ->seconds(false);
    }
}
