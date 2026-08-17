<?php

declare(strict_types=1);

namespace App\Services\ApReport;

use function array_filter;
use function array_values;
use function count;

/**
 * A chapter of the AP notification form, holding the answers in the order the
 * online form asks them.
 */
class ApChapter
{
    /**
     * @param array<int, ApAnswer> $answers
     */
    public function __construct(
        public readonly string $number,
        public readonly string $title,
        public readonly array $answers,
    ) {
    }

    /**
     * @return array<int, ApAnswer>
     */
    public function missingAnswers(): array
    {
        return array_values(array_filter(
            $this->answers,
            static fn (ApAnswer $answer): bool => $answer->isMissing(),
        ));
    }

    public function missingCount(): int
    {
        return count($this->missingAnswers());
    }
}
