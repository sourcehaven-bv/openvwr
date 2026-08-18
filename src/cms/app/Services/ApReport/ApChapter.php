<?php

declare(strict_types=1);

namespace App\Services\ApReport;

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
}
