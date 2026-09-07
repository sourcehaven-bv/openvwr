<?php

declare(strict_types=1);

namespace App\Import\Mapping;

/**
 * A spreadsheet reduced to its header row and its data rows.
 */
readonly class Sheet
{
    /**
     * @param array<int, string> $headers
     * @param array<int, array<string, mixed>> $rows
     */
    public function __construct(
        public array $headers,
        public array $rows,
    ) {
    }
}
