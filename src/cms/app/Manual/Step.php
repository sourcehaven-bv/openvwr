<?php

declare(strict_types=1);

namespace App\Manual;

/**
 * One step of a task.
 *
 * A step says what to do in a sentence or two and then points at the reference
 * topics that explain it. The explanation itself lives in those topics, never
 * here: a step that grows into a paragraph belongs in the naslag instead.
 *
 * A gate hides the step on its own, for a step that only applies while a
 * feature flag is on. The task and its other steps stay.
 */
readonly class Step
{
    /**
     * @param array<non-empty-string> $topicIds reference topics this step links to
     */
    public function __construct(
        public string $title,
        public string $body,
        public array $topicIds = [],
        public ?FeatureGate $gate = null,
    ) {
    }

    public function isVisible(): bool
    {
        return $this->gate === null || $this->gate->enabled();
    }
}
