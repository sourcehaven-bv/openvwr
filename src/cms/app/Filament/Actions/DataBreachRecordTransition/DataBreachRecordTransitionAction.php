<?php

declare(strict_types=1);

namespace App\Filament\Actions\DataBreachRecordTransition;

use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Filament\Resources\DataBreachRecordResource;
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
            ->action(static function () use ($dataBreachRecord, $dataBreachRecordState): void {
                $dataBreachRecord->state->transitionTo($dataBreachRecordState);
            })
            ->after(static function (Action $action) use ($dataBreachRecord): void {
                // Which transitions are offered depends on the state, so the
                // page is reloaded to rebuild the header actions.
                $action->redirect(DataBreachRecordResource::getUrl('view', ['record' => $dataBreachRecord]));
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
