<?php

declare(strict_types=1);

namespace App\Import\Mapping;

/**
 * A row that did not map cleanly, with the reason stated in terms of the source
 * column so the user knows what to re-map.
 */
readonly class DryRunIssue
{
    /**
     * @param array<string, mixed> $row the original source row
     */
    public function __construct(
        public int $rowNumber,
        public array $row,
        public string $reason,
    ) {
    }
}
