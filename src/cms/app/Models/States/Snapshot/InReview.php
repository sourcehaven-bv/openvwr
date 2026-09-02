<?php

declare(strict_types=1);

namespace App\Models\States\Snapshot;

use App\Enums\StateColor;
use App\Models\States\SnapshotState;

class InReview extends SnapshotState
{
    public static string $name = 'in_review';
    public static StateColor $color = StateColor::INFO;
}
