<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use function count;

/**
 * The outcome of a dry-run: rows that map cleanly, and rows the user still has
 * to decide about. Nothing is persisted.
 */
readonly class DryRunResult
{
    /**
     * @param array<int, array{number: int, attributes: array<string, mixed>, row: array<string, mixed>}> $fits
     *        rows ready to import, numbered as in the sheet, with the source row
     *        kept so relations can still be resolved from columns that are not
     *        plain attributes
     * @param array<int, DryRunIssue> $issues rows needing attention
     */
    public function __construct(
        public array $fits,
        public array $issues,
    ) {
    }

    public function fitCount(): int
    {
        return count($this->fits);
    }

    public function issueCount(): int
    {
        return count($this->issues);
    }

    public function hasIssues(): bool
    {
        return $this->issues !== [];
    }
}
