<?php

declare(strict_types=1);

namespace App\Filament\Actions\Exports;

use App\Models\Contracts\SnapshotSource;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Webmozart\Assert\Assert;

use function __;

class SnapshotLatestCreatedAtColumn extends ExportColumn
{
    public static function make(?string $name = 'snapshot_latest_status_created_at'): static
    {
        return parent::make($name)
            ->label(__('snapshot.latest_status_created_at'))
            ->default(static function (Model $model): ?CarbonInterface {
                Assert::isInstanceOf($model, SnapshotSource::class);

                $snapshot = $model->snapshots()
                    ->orderBy('version', 'desc')
                    ->first();

                return $snapshot?->created_at;
            });
    }
}
