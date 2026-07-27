<?php

declare(strict_types=1);

namespace App\Models\States\Transitions\DataBreachRecord;

use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecord\Closed;

class ClosedTransition extends DataBreachRecordStateTransition
{
    public function handle(): DataBreachRecord
    {
        $this->transitionToState(Closed::class);

        return $this->dataBreachRecord;
    }
}
