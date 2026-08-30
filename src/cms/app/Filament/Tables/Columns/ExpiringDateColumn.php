<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use App\Services\DateFormatService;
use App\ValueObjects\CalendarDate;
use Carbon\CarbonInterface;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

class ExpiringDateColumn extends TextColumn
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->date(DateFormatService::FORMAT_DATE, DateFormatService::getDisplayTimezone())
            ->color(static function (Model $model) use ($name): ?string {
                $attribute = $model->getAttribute($name);
                Assert::nullOrIsInstanceOfAny($attribute, [
                    CalendarDate::class,
                    CarbonInterface::class,
                ]);

                if ($attribute === null) {
                    return null;
                }

                return $attribute->isPast() ? 'danger' : null;
            })
            ->sortable();
    }
}
