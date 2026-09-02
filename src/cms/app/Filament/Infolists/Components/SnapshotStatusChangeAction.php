<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Facades\Authorization;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\Established;
use App\Models\States\SnapshotState;
use App\Services\Snapshot\SnapshotStateTransitionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Livewire\Component;
use Webmozart\Assert\Assert;

use function __;
use function sprintf;

/**
 * The single "Status aanpassen" button that sits beside the status flow.
 *
 * It used to be a dropdown in the page header, one item per reachable state. Next to the
 * flow it reads as what it is — the way to move the snapshot along the line drawn right
 * there — so it became one button opening a modal that lists the same reachable states.
 */
class SnapshotStatusChangeAction extends Action
{
    public static function make(?string $name = 'snapshot_status_change'): static
    {
        return parent::make($name)
            ->label(__('snapshot.status_change'))
            ->icon('heroicon-o-arrow-path')
            ->modalHeading(__('snapshot.status_change'))
            ->modalWidth(MaxWidth::Large)
            ->modalSubmitActionLabel(__('snapshot.status_change_confirm'))
            ->visible(static function (Snapshot $record): bool {
                return self::getTransitionableStates($record) !== [];
            })
            ->form(static function (Snapshot $record): array {
                return [
                    Radio::make('state')
                        ->label(__('snapshot.status_change_target'))
                        ->options(self::getTransitionableStates($record))
                        ->required(),
                ];
            })
            ->action(static function (
                array $data,
                Snapshot $record,
                SnapshotStateTransitionService $snapshotStateTransitionService,
            ): void {
                $stateName = $data['state'];
                Assert::string($stateName);

                // Checked again here rather than trusted from the form: the options are
                // what the page offers, not what a request has to contain. Establishing
                // has its own button with two checks in front of it, and this is the
                // list's only way to reach a state, so it may not slip through here.
                Assert::keyExists(self::getTransitionableStates($record), $stateName);

                $snapshotStateTransitionService->transitionToSnapshotState(
                    $record,
                    self::resolveState($record, $stateName),
                );
            })
            ->after(static function (Component $livewire): void {
                $livewire->dispatch(ViewSnapshot::REFRESH_LIVEWIRE_COMPONENT);
            });
    }

    private static function resolveState(Snapshot $snapshot, string $stateName): SnapshotState
    {
        $snapshotState = SnapshotState::make($stateName, $snapshot);
        Assert::isInstanceOf($snapshotState, SnapshotState::class);

        return $snapshotState;
    }

    /**
     * The reachable states, labelled as the transition the user is choosing, in the same
     * order the status flow draws them. Only states the user is allowed to move to are
     * offered.
     *
     * A concept has none: it leaves for review from the record's own form ("Start
     * vaststellen"), which is the only place its required fields can be filled in.
     *
     * @return array<string, string>
     */
    private static function getTransitionableStates(Snapshot $snapshot): array
    {
        if ($snapshot->state instanceof Concept) {
            return [];
        }

        $options = [];

        foreach ($snapshot->state->orderedTransitionableStates() as $transitionableState) {
            // Vaststellen has its own button (SnapshotEstablishAction), because it first
            // walks the user through the related entities and the approvals. Offering it
            // here as well would be a second, unchecked way to do the same thing.
            if ($transitionableState === Established::$name) {
                continue;
            }

            $snapshotState = self::resolveState($snapshot, $transitionableState);

            if (!Authorization::hasPermission($snapshotState::$requiredPermission)) {
                continue;
            }

            $options[$transitionableState] = __(sprintf('snapshot_state.transition.%s', $transitionableState));
        }

        return $options;
    }
}
