<?php

declare(strict_types=1);

namespace App\Models\States\Transitions\DataBreachRecord;

use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecord\Reported;

class ReportedTransition extends DataBreachRecordStateTransition
{
    public function handle(): DataBreachRecord
    {
        $this->transitionToState(Reported::class);

        return $this->dataBreachRecord;
    }
}
