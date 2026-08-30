<?php

declare(strict_types=1);

namespace App\Filament\Pages\Contracts;

/**
 * Marks a page whose record may be saved as a concept, even half-finished.
 *
 * Required fields are not enforced when saving such a record; they are enforced when
 * a version (snapshot) is created, which is the moment the record enters the approval
 * process and must be complete. See DraftableForm and SnapshotReadinessService.
 */
interface SavesConcepts
{
}
