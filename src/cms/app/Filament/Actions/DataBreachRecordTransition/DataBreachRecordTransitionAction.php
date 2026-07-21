<?php

declare(strict_types=1);

namespace App\Filament\Actions\DataBreachRecordTransition;

use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
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
            ->label(__(sprintf('data_breach_record_state.transition.%s', $dataBreachRecordState::$name)))
            ->visible(Authorization::hasPermission(Permission::DATA_BREACH_RECORD_UPDATE))
            ->action(static function () use ($dataBreachRecord, $dataBreachRecordState): void {
                $dataBreachRecord->state->transitionTo($dataBreachRecordState);
            })
            ->requiresConfirmation();
    }
}
