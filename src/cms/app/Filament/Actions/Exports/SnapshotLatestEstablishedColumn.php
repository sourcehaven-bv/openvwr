<?php

declare(strict_types=1);

namespace App\Filament\Actions\Exports;

use App\Models\Contracts\SnapshotSource;
use App\Models\States\Snapshot\Established;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

use function __;
use function assert;

class SnapshotLatestEstablishedColumn extends ExportColumn
{
    public static function make(?string $name = 'snapshot_latest_established'): static
    {
        return parent::make($name)
            ->label(__('snapshot.latest_established'))
            ->default(static function (Model $model): ?CarbonInterface {
                assert($model instanceof SnapshotSource);
                $snapshot = $model->getLatestSnapshotWithState([Established::class]);

                return $snapshot?->created_at;
            });
    }
}
