<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SnapshotResource\Pages\CompareSnapshots;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Snapshot;

use function __;

class SnapshotResource extends Resource
{
    protected static ?string $model = Snapshot::class;
    protected static bool $shouldRegisterNavigation = false;

    public static function getPages(): array
    {
        return [
            'view' => ViewSnapshot::route('/{record}'),
            'compare' => CompareSnapshots::route('/{record}/compare'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('snapshot.model_singular');
    }
}
