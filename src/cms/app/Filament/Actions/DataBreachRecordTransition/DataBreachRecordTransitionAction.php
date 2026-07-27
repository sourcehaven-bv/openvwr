<?php

declare(strict_types=1);

namespace App\Filament\Actions\DataBreachRecordTransition;

use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Filament\Resources\DataBreachRecord\Pages\Contracts\RefreshesDataBreachRecordWorkflow;
use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecordState;
use Filament\Actions\Action;

use function __;
use function sprintf;

abstract class DataBreachRecordTransitionAction extends Action
{
    public static function makeForDataBreachRecordState(
        DataBreachRecord $dataBreachRecord,
        DataBreachRecordState $dataBreachRecordState,
    ): static {
        return parent::make(sprintf('data_breach_record_transition_to_%s', $dataBreachRecordState::$name))
            ->color($dataBreachRecordState::$color->value)
            ->label(self::getTransitionLabel($dataBreachRecord->state, $dataBreachRecordState))
            ->visible(Authorization::hasPermission(Permission::DATA_BREACH_RECORD_UPDATE))
            ->action(static function (Action $action) use ($dataBreachRecord, $dataBreachRecordState): void {
                $dataBreachRecord->state->transitionTo($dataBreachRecordState);

                // Deliberately no redirect: the record is edited on this same
                // page, and a page load would throw away unsaved form input.
                // Instead the page refreshes its own record and rebuilds its
                // header actions, so the dropdown offers the transitions of the
                // new state while the form keeps whatever was typed into it.
                $livewire = $action->getLivewire();

                if ($livewire instanceof RefreshesDataBreachRecordWorkflow) {
                    $livewire->refreshDataBreachRecordHeaderActions();
                }
            })
            ->requiresConfirmation();
    }

    /**
     * Moving back through the workflow is a correction, not a repeat of the
     * original step, so it gets its own wording.
     */
    private static function getTransitionLabel(
        DataBreachRecordState $currentState,
        DataBreachRecordState $targetState,
    ): string {
        $isCorrection = $targetState::$position > 0
            && $currentState::$position > 0
            && $targetState::$position < $currentState::$position;

        $key = $isCorrection ? 'transition_back' : 'transition';

        return __(sprintf('data_breach_record_state.%s.%s', $key, $targetState::$name));
    }
}
