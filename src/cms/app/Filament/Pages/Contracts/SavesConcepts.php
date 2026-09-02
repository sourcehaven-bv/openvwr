<?php

declare(strict_types=1);

namespace App\Filament\Pages\Contracts;

/**
 * Marks a page whose record may be saved as a concept, even half-finished.
 *
 * Required fields are not enforced when saving such a record; saving only refreshes its
 * concept snapshot. They are enforced when that concept is sent to review, which is the
 * moment the record enters the approval process and must be complete. See DraftableForm
 * and SnapshotReadinessService.
 */
interface SavesConcepts
{
    /**
     * Whether required fields must be enforced for the validation happening right now.
     *
     * False while saving a concept, true while submitting it for review. Keeping this on
     * the page rather than in global state means it cannot leak across requests.
     */
    public function enforcesRequiredFields(): bool;

    /**
     * Runs the callback with required fields enforced, restoring the concept behaviour
     * afterwards.
     *
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function withRequiredFieldsEnforced(callable $callback): mixed;
}
