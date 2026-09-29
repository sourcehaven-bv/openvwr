<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use App\Models\States\SnapshotState;
use Filament\Tables\Columns\TextColumn;

use function __;
use function sprintf;

class SnapshotStateColumn extends TextColumn
{
    public static function make(?string $name = 'state'): static
    {
        return parent::make($name)
            ->label(__('snapshot.state'))
            ->badge()
            ->sortable()
            ->alignCenter()
            ->color(static function (SnapshotState $state): string {
                return $state::$color->value;
            })
            ->formatStateUsing(static function (string $state): string {
                return __(sprintf('snapshot_state.label.%s', $state));
            });
    }
}
