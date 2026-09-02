<?php

declare(strict_types=1);

namespace App\Models\States\Snapshot;

use App\Enums\Authorization\Permission;
use App\Enums\StateColor;
use App\Models\States\SnapshotState;

class Established extends SnapshotState
{
    public static string $name = 'established';
    public static StateColor $color = StateColor::SUCCESS;
    public static Permission $requiredPermission = Permission::SNAPSHOT_STATE_TO_ESTABLISHED;
}
