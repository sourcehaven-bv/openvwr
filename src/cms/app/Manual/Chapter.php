<?php

declare(strict_types=1);

namespace App\Manual;

use function array_filter;
use function array_values;

/**
 * A chapter of the reference layer, holding one or more topics.
 */
readonly class Chapter
{
    /**
     * @param non-empty-string $id
     * @param array<Topic> $topics
     */
    public function __construct(
        public string $id,
        public string $title,
        public string $summary,
        public array $topics,
    ) {
    }

    /**
     * The topics whose feature flag is on. A chapter that keeps no topics is
     * dropped by the manual itself.
     *
     * @return array<Topic>
     */
    public function visibleTopics(): array
    {
        return array_values(array_filter(
            $this->topics,
            static fn (Topic $topic): bool => $topic->isVisible(),
        ));
    }
}
