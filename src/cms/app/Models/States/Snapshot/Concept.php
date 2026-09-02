<?php

declare(strict_types=1);

namespace App\Models\States\Snapshot;

use App\Enums\StateColor;
use App\Models\States\SnapshotState;

/**
 * The state every snapshot starts in: an automatically maintained concept.
 *
 * Saving an entity refreshes its concept snapshot instead of creating a new one, so
 * there is always exactly one concept per entity that mirrors the current draft. It
 * leaves concept the only state a user never transitions *to* — the state machine has
 * no edge back into it.
 */
class Concept extends SnapshotState
{
    public static string $name = 'concept';
    public static StateColor $color = StateColor::WARNING;
}
