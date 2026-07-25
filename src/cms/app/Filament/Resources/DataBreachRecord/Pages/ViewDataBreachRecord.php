<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord\Pages;

use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Resources\DataBreachRecordResource;
use App\Filament\Widgets\FgRemarksWidget;
use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecordState;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Spatie\ModelStates\Exceptions\InvalidConfig;

class ViewDataBreachRecord extends ViewRecord
{
    protected static string $resource = DataBreachRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
            ...$this->getDataBreachRecordWorkflowActions(),
            DeleteAction::make(),
        ];
    }

    /**
     * @return array<Action>
     *
     * @throws InvalidConfig
     */
    private function getDataBreachRecordWorkflowActions(): array
    {
        /** @var DataBreachRecord $dataBreachRecord */
        $dataBreachRecord = $this->record;
        /** @var array<int, string> $transitionableStates */
        $transitionableStates = $dataBreachRecord->state->transitionableStates();

        $actions = [];
        foreach ($transitionableStates as $transitionableState) {
            /** @var DataBreachRecordState $dataBreachRecordState */
            $dataBreachRecordState = DataBreachRecordState::make($transitionableState, $dataBreachRecord);
            $action = $dataBreachRecordState::getAction();

            $actions[] = $action::makeForDataBreachRecordState($dataBreachRecord, $dataBreachRecordState);
        }

        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FgRemarksWidget::class,
        ];
    }
}
