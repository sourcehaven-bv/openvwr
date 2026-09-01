<?php

declare(strict_types=1);

namespace App\Filament\Pages\Concerns;

/**
 * Lets a concept page validate strictly for the duration of one action.
 *
 * Saving a concept ignores required fields; submitting it for review does not. Both go
 * through the same form, so the page carries the distinction as request-scoped state
 * rather than a global flag that could leak between requests.
 *
 * @see \App\Filament\Forms\DraftableForm which reads this
 */
trait EnforcesRequiredFieldsWhenSubmitting
{
    private bool $enforcesRequiredFields = false;

    final public function enforcesRequiredFields(): bool
    {
        return $this->enforcesRequiredFields;
    }

    /**
     * Runs the callback with required fields enforced, restoring the concept behaviour
     * afterwards so a failed submit leaves the page saveable as a concept again.
     *
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    final public function withRequiredFieldsEnforced(callable $callback): mixed
    {
        $this->enforcesRequiredFields = true;

        try {
            return $callback();
        } finally {
            $this->enforcesRequiredFields = false;
        }
    }
}
