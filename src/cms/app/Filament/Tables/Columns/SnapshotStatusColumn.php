<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use Filament\Tables\Columns\ViewColumn;

use function __;

class SnapshotStatusColumn extends ViewColumn
{
    public static function make(?string $name = 'snapshot_latest'): static
    {
        return parent::make($name)
            ->label(__('snapshot.state'))
            ->view('filament.tables.columns.snapshot_status');
    }
}
