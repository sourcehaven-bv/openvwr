<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord\Pages\Concerns;

use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecordState;
use Filament\Actions\ActionGroup;
use Spatie\ModelStates\Exceptions\InvalidConfig;
use Webmozart\Assert\Assert;

use function __;
use function sprintf;

trait HasDataBreachRecordWorkflowActions
{
    /**
     * Bring the workflow actions back in step with the record after a
     * transition. The header actions are cached once when the page boots, so
     * without this the dropdown would keep offering the transitions of the state
     * the record was in before.
     */
    final public function refreshDataBreachRecordHeaderActions(): void
    {
        $dataBreachRecord = $this->getRecord();
        Assert::isInstanceOf($dataBreachRecord, DataBreachRecord::class);
        $dataBreachRecord->refresh();

        $this->cachedHeaderActions = [];
        $this->cacheHeaderActions();
    }

    /**
     * A single dropdown grouping every reachable transition. The trigger is
     * labelled with the record's current state; opening it lists the transitions
     * that are possible from there.
     *
     * @return array<ActionGroup>
     *
     * @throws InvalidConfig
     */
    final protected function getDataBreachRecordWorkflowActions(): array
    {
        $dataBreachRecord = $this->getRecord();
        Assert::isInstanceOf($dataBreachRecord, DataBreachRecord::class);

        $currentState = $dataBreachRecord->state;

        $actions = [];
        foreach ($currentState->orderedTransitionableStates() as $transitionableState) {
            /** @var DataBreachRecordState $dataBreachRecordState */
            $dataBreachRecordState = DataBreachRecordState::make($transitionableState, $dataBreachRecord);
            $action = $dataBreachRecordState::getAction();

            $actions[] = $action::makeForDataBreachRecordState($dataBreachRecord, $dataBreachRecordState);
        }

        // No empty-list guard: every state in this workflow can be left again,
        // so there is always at least one transition to offer.
        return [
            ActionGroup::make($actions)
                ->label(__(sprintf('data_breach_record_state.label.%s', $currentState::$name)))
                ->color($currentState::$color->value)
                ->button(),
        ];
    }
}
