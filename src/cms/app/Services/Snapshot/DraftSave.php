<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use Throwable;

/**
 * Marks the section of a request during which a record is saved as a concept.
 *
 * Saving a half-finished record is a deliberate feature: a user may fill in what they
 * know, save, and come back later. Required fields are therefore not enforced while
 * this flag is active; they are enforced when a version (snapshot) is created, which
 * is the moment the record enters the approval process and must be complete.
 *
 * The `->required()` declarations in the form schemas stay untouched and remain the
 * single source of truth; this only suppresses their enforcement while saving a draft.
 */
class DraftSave
{
    private static bool $isSavingDraft = false;

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     *
     * @throws Throwable
     */
    public static function whileSavingDraft(callable $callback): mixed
    {
        $previous = self::$isSavingDraft;
        self::$isSavingDraft = true;

        try {
            return $callback();
        } finally {
            self::$isSavingDraft = $previous;
        }
    }

    public static function isSavingDraft(): bool
    {
        return self::$isSavingDraft;
    }
}
