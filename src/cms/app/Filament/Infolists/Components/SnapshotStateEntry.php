<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Models\States\SnapshotState;
use Filament\Infolists\Components\TextEntry;

use function __;
use function sprintf;

class SnapshotStateEntry extends TextEntry
{
    public static function make(?string $name = 'state'): static
    {
        return parent::make($name)
            ->label(__('snapshot.state'))
            ->badge()
            ->color(static function (SnapshotState $state): string {
                return $state::$color->value;
            })
            ->formatStateUsing(static function (string $state): string {
                return __(sprintf('snapshot_state.label.%s', $state));
            });
    }
}
