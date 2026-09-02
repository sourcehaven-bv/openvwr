<?php

declare(strict_types=1);

namespace App\Models\States\Snapshot;

use App\Enums\Authorization\Permission;
use App\Enums\StateColor;
use App\Models\States\SnapshotState;

class Obsolete extends SnapshotState
{
    public static string $name = 'obsolete';
    public static StateColor $color = StateColor::GRAY;
    public static Permission $requiredPermission = Permission::SNAPSHOT_STATE_TO_OBSOLETE;
}
