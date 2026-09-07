<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use function count;

/**
 * What an import wrote, and what it could not.
 *
 * Failures are per row and carry the row number, so a user can see which rows
 * are missing instead of being told a total that does not add up.
 */
readonly class WriteResult
{
    /**
     * @param int $imported rows written
     * @param int $skipped rows left alone because their source reference was
     *        imported before
     * @param array<int, array{row: int, reason: string}> $failures rows that
     *        could not be written; each was rolled back on its own
     * @param array<string, array<int, string>> $newEntities per model class
     * @param array<string, array<int, array{source: string, matched: string}>> $fuzzyEntities per model class
     * @param array<string, array<string, int>> $ambiguousEntities per model class
     * @param array<string, array<int, string>> $unresolvedEntities per model class
     */
    public function __construct(
        public int $imported,
        public int $skipped,
        public array $failures,
        public array $newEntities,
        public array $fuzzyEntities,
        public array $ambiguousEntities,
        public array $unresolvedEntities,
    ) {
    }

    public function failureCount(): int
    {
        return count($this->failures);
    }

    /**
     * The entity findings in one array, shaped for the result screen.
     *
     * @return array{new: array<string, array<int, string>>, fuzzy: array<string, array<int, array{source: string, matched: string}>>, ambiguous: array<string, array<string, int>>, unresolved: array<string, array<int, string>>}
     */
    public function entityReport(): array
    {
        return [
            'new' => $this->newEntities,
            'fuzzy' => $this->fuzzyEntities,
            'ambiguous' => $this->ambiguousEntities,
            'unresolved' => $this->unresolvedEntities,
        ];
    }
}
