<?php

declare(strict_types=1);

namespace App\FixedLists;

/**
 * A single entry of a fixed list.
 *
 * Entries are never removed from a list once they have been used: instead they are retired, so that
 * records still holding the value keep validating while the value is no longer offered for new input.
 */
class FixedListEntry
{
    public function __construct(
        public readonly string $value,
        public readonly ?string $retiredReason = null,
    ) {
    }

    public static function current(string $value): self
    {
        return new self($value);
    }

    public static function retired(string $value, string $reason): self
    {
        return new self($value, $reason);
    }

    public function isRetired(): bool
    {
        return $this->retiredReason !== null;
    }
}
