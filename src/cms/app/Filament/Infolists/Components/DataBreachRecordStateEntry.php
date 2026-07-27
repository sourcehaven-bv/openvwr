<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Models\States\DataBreachRecordState;
use Filament\Infolists\Components\TextEntry;

use function __;
use function sprintf;

class DataBreachRecordStateEntry extends TextEntry
{
    public static function make(string $name = 'state'): static
    {
        return parent::make($name)
            ->label(__('data_breach_record.state'))
            ->badge()
            ->color(static function (DataBreachRecordState $state): string {
                return $state::$color->value;
            })
            ->formatStateUsing(static function (string $state): string {
                return __(sprintf('data_breach_record_state.label.%s', $state));
            });
    }
}
