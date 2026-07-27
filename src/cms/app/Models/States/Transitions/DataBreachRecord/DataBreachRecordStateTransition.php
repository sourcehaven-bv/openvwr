<?php

declare(strict_types=1);

namespace App\Models\States\Transitions\DataBreachRecord;

use App\Components\Uuid\UuidInterface;
use App\Models\DataBreachRecord;
use App\Models\DataBreachRecordTransition;
use App\Models\States\DataBreachRecordState;
use App\Models\User;
use Filament\Facades\Filament;
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

        DataBreachRecordTransition::create([
            'data_breach_record_id' => $this->dataBreachRecord->id,
            'created_by' => $this->getActingUserId(),
            'state' => $this->dataBreachRecord->state,
        ]);
    }

    /**
     * Transitions are normally made by a signed-in user, but can also happen
     * outside a web request (seeders, console commands) where there is none.
     */
    private function getActingUserId(): ?UuidInterface
    {
        $user = Filament::auth()->user();

        return $user instanceof User ? $user->id : null;
    }
}
