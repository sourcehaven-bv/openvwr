<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Config\Config;
use App\Config\Feature;
use App\Facades\Authentication;
use App\Filament\Forms\Components\DatePicker\DateTimePicker;
use App\Services\DateFormatService;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;

use function __;

class PublicFromField extends DateTimePicker
{
    /**
     * @param class-string<Model> $model
     */
    public static function makeForModel(string $model, string $name = 'public_from'): static
    {
        return parent::make($name)
            ->label(__('general.public_from'))
            ->hintAction(
                Action::make('public_from_set_now')
                    ->label(__('general.public_from_set_now'))
                    ->icon('heroicon-m-clock')
                    ->visible(static function (string $operation, ?Model $record) use ($model): bool {
                        if ($operation === 'create') {
                            return Authentication::user()->can('create', $model);
                        }

                        return Authentication::user()->can('update', $record);
                    })
                    ->action(static function (Set $set): void {
                        $publicFromNow = CarbonImmutable::now(Config::string('app.display_timezone'))
                            ->floorMinute()
                            ->format(DateFormatService::FORMAT_DATE_TIME_INPUT);

                        $set('public_from', $publicFromNow);
                    }),
            )
            ->visible(Feature::publishingEnabled());
    }
}
