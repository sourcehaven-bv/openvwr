<?php

declare(strict_types=1);

namespace App\Models\States\Transitions\DataBreachRecord;

use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecord\InResponse;

class InResponseTransition extends DataBreachRecordStateTransition
{
    public function handle(): DataBreachRecord
    {
        $this->transitionToState(InResponse::class);

        return $this->dataBreachRecord;
    }
}
