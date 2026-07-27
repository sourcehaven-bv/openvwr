<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use App\Models\States\DataBreachRecordState;
use Filament\Tables\Columns\TextColumn;

use function __;
use function sprintf;

class DataBreachRecordStateColumn extends TextColumn
{
    public static function make(string $name = 'state'): static
    {
        return parent::make($name)
            ->label(__('data_breach_record.state'))
            ->badge()
            ->sortable()
            ->alignCenter()
            ->color(static function (DataBreachRecordState $state): string {
                return $state::$color->value;
            })
            ->formatStateUsing(static function (string $state): string {
                return __(sprintf('data_breach_record_state.label.%s', $state));
            });
    }
}
