<?php

declare(strict_types=1);

namespace App\Transfer\Import;

class TransferImportResult
{
    public function __construct(
        public int $created = 0,
        public int $overwritten = 0,
        public int $skipped = 0,
    ) {
    }
}
