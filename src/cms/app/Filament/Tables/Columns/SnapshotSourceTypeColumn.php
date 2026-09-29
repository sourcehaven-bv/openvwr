<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

use function __;
use function class_basename;
use function sprintf;

class SnapshotSourceTypeColumn extends TextColumn
{
    public static function make(?string $name = 'snapshot_source_type'): static
    {
        return parent::make($name)
            ->label(__('snapshot.snapshot_source_type'))
            ->sortable()
            ->formatStateUsing(static function (string $state): string {
                return __(sprintf('%s.model_singular', Str::snake(class_basename($state))));
            });
    }
}
