<?php

declare(strict_types=1);

namespace App\Models\States\Transitions;

use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use Carbon\CarbonImmutable;

class EstablishedTransition extends StateTransition
{
    public function handle(): Snapshot
    {
        $this->snapshot->established_at = CarbonImmutable::now();
        $this->transitionToState(Established::class);

        return $this->snapshot;
    }
}
