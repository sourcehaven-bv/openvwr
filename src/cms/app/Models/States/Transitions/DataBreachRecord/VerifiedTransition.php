<?php

declare(strict_types=1);

namespace App\Models\States\Transitions\DataBreachRecord;

use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecord\Verified;

class VerifiedTransition extends DataBreachRecordStateTransition
{
    public function handle(): DataBreachRecord
    {
        $this->transitionToState(Verified::class);

        return $this->dataBreachRecord;
    }
}
