<?php

declare(strict_types=1);

namespace App\Filament\Pages\Concerns;

use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Models\Contracts\SnapshotSource;
use App\Services\Snapshot\ConceptSnapshotService;
use Illuminate\Support\Facades\App;

/**
 * Writes the record's concept snapshot after every save.
 *
 * There is always a version: saving a record creates its concept snapshot, or updates
 * the existing one when it is still a concept. This is what replaced the old "Versie
 * aanmaken" button — the user no longer has to ask for a version, only to move the
 * concept on to review when it is ready.
 *
 * Records that are not versioned (the data-breach register uses these same concept
 * pages) are skipped, hence the interface check.
 *
 * Shared by the concept create and edit pages, which call it from their own respective
 * save hooks.
 */
trait StoresConceptSnapshot
{
    final protected function storeConceptSnapshot(): void
    {
        $record = $this->getRecord();
        if (!$record instanceof SnapshotSource) {
            return;
        }

        /** @var ConceptSnapshotService $conceptSnapshotService */
        $conceptSnapshotService = App::get(ConceptSnapshotService::class);
        $conceptSnapshotService->storeConcept($record);

        $this->dispatch(SnapshotsRelationManager::REFRESH_TABLE_EVENT);
    }
}
