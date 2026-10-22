<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use App\Services\DateFormatService;
use Filament\Tables\Columns\TextColumn;

use function __;

class UpdatedAtColumn extends TextColumn
{
    public static function make(?string $name = 'state'): static
    {
        return parent::make('updated_at')
            ->label(__('general.updated_at'))
            ->dateTime(DateFormatService::FORMAT_DATE_TIME, DateFormatService::getDisplayTimezone())
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
