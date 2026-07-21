<?php

declare(strict_types=1);

namespace App\Models\States\Transitions\DataBreachRecord;

use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecordState;
use Spatie\ModelStates\Transition;

abstract class DataBreachRecordStateTransition extends Transition
{
    public function __construct(
        protected readonly DataBreachRecord $dataBreachRecord,
    ) {
    }

    abstract public function handle(): DataBreachRecord;

    /**
     * @param class-string<DataBreachRecordState> $stateClass
     */
    protected function transitionToState(string $stateClass): void
    {
        $this->dataBreachRecord->state = new $stateClass($this->dataBreachRecord);
        $this->dataBreachRecord->save();
    }
}
