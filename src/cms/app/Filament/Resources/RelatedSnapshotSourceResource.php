<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RelatedSnapshotSourceResource\RelatedSnapshotSourceResourceTable;
use App\Models\RelatedSnapshotSource;
use Filament\Tables\Table;

class RelatedSnapshotSourceResource extends Resource
{
    protected static ?string $model = RelatedSnapshotSource::class;
    protected static bool $shouldRegisterNavigation = false;

    /**
     * A related snapshot source belongs to a snapshot, not to an organisation:
     * it is reached through the snapshot that already carries the tenant. v5
     * registers a `creating` observer for every tenant-scoped resource and that
     * one insists on an `organisation` relationship this join model does not
     * have, so it is told the scoping happens elsewhere.
     */
    protected static bool $isScopedToTenant = false;

    public static function table(Table $table): Table
    {
        return RelatedSnapshotSourceResourceTable::table($table);
    }
}
