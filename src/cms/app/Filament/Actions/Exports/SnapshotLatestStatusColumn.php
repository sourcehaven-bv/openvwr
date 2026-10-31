<?php

declare(strict_types=1);

namespace App\Filament\Actions\Exports;

use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use Illuminate\Database\Eloquent\Model;

use function __;
use function assert;
use function sprintf;

class SnapshotLatestStatusColumn extends ExportColumn
{
    public static function make(?string $name = 'snapshot_latest_status'): static
    {
        return parent::make($name)
            ->label(__('snapshot.latest_status'))
            ->default(static function (Model $model): ?string {
                assert($model instanceof SnapshotSource);
                $snapshot = $model->snapshots()
                    ->orderBy('version', 'desc')
                    ->first();

                if ($snapshot instanceof Snapshot) {
                    return __(sprintf('snapshot_state.label.%s', $snapshot->state::$name));
                }

                return null;
            });
    }
}
