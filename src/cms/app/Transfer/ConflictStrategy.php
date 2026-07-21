<?php

declare(strict_types=1);

namespace App\Transfer;

enum ConflictStrategy: string
{
    case SKIP = 'skip';
    case OVERWRITE = 'overwrite';
    case COPY = 'copy';
}
