<?php

declare(strict_types=1);

namespace App\Models\States\Transitions\DataBreachRecord;

use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecord\NoBreach;

class NoBreachTransition extends DataBreachRecordStateTransition
{
    public function handle(): DataBreachRecord
    {
        $this->transitionToState(NoBreach::class);

        return $this->dataBreachRecord;
    }
}
