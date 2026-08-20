<?php

declare(strict_types=1);

namespace App\FixedLists\Audit;

class FixedListFinding
{
    public function __construct(
        public readonly string $model,
        public readonly string $column,
        public readonly string $value,
        public readonly FixedListFindingType $type,
        public readonly int $count,
        public readonly ?string $reason = null,
    ) {
    }
}
