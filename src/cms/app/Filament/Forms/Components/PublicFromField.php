<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Config\Feature;
use App\Facades\Authentication;
use App\Filament\Forms\Components\DatePicker\DateTimePicker;
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
                        // Set accepts model state in v5 and applies the picker's
                        // timezone cast. Keep the instant instead of passing an
                        // already-localised string through that cast twice.
                        $set('public_from', CarbonImmutable::now()->floorMinute());
                    }),
            )
            ->visible(Feature::publishingEnabled());
    }
}
