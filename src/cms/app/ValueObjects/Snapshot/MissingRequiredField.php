<?php

declare(strict_types=1);

namespace App\ValueObjects\Snapshot;

use function __;
use function sprintf;

/**
 * A required form field that is still empty, together with the step it lives on,
 * so the user can be told concretely what to complete before creating a version.
 */
readonly class MissingRequiredField
{
    public function __construct(
        public string $statePath,
        public string $label,
        public ?string $stepLabel,
    ) {
    }

    public function describe(): string
    {
        if ($this->stepLabel === null) {
            return $this->label;
        }

        return sprintf('%s (%s)', $this->label, __('snapshot.incomplete_step', ['step' => $this->stepLabel]));
    }
}
