<?php

declare(strict_types=1);

namespace App\Manual;

/**
 * One step of a task.
 *
 * A step says what to do in a sentence or two and then points at the reference
 * topics that explain it. The explanation itself lives in those topics, never
 * here: a step that grows into a paragraph belongs in the naslag instead.
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
    ) {
    }
}
