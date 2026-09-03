<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Facades\Authorization;
use App\Facades\Snapshot as SnapshotFacade;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use App\Models\States\SnapshotState;
use App\Services\Snapshot\SnapshotStateTransitionService;
use Filament\Actions\Action;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Webmozart\Assert\Assert;

use function __;
use function in_array;

/**
 * Establishing a version, kept apart from the other status changes.
 *
 * Vaststellen is the one transition with something to check first: the related entities
 * have to be established too, and the mandate holders' approvals have to be in. Those
 * checks are two steps the user walks through, which is why this cannot be another
 * option in the "Status aanpassen" list — that one is a single radio and has nothing to
 * show. Skipping the steps would let a version be established while the things it points
 * at are still unfinished.
 */
class SnapshotEstablishAction extends Action
{
    public static function make(?string $name = 'snapshot_establish'): static
    {
        return parent::make($name)
            ->label(__('snapshot_state.transition.established'))
            ->icon('heroicon-o-check-badge')
            // Green only once both checks pass, so the button itself says whether this
            // version is ready to be established or still waiting on something.
            ->color(static function (Snapshot $record): string {
                return self::isReadyToEstablish($record) ? 'success' : 'warning';
            })
            ->visible(static function (Snapshot $record): bool {
                return self::isEstablishable($record);
            })
            ->steps([
                Step::make(__('snapshot_transition.establish.step_1'))
                    ->description(__('snapshot_transition.establish.validate_related_snapshot_sources'))
                    ->schema([
                        View::make('filament.actions.snapshot_transition.establish_action_step_validate_related_snapshot_sources')
                            ->view('filament.actions.snapshot_transition.establish_action_step_validate_related_snapshot_sources'),
                    ]),
                Step::make(__('snapshot_transition.establish.step_2'))
                    ->description(__('snapshot_transition.establish.validate_approvals'))
                    ->schema([
                        View::make('filament.actions.snapshot_transition.establish_action_step_validate_approvals')
                            ->view('filament.actions.snapshot_transition.establish_action_step_validate_approvals'),
                    ]),
            ])
            ->modalWidth(Width::FiveExtraLarge)
            ->action(static function (
                Snapshot $record,
                SnapshotStateTransitionService $snapshotStateTransitionService,
            ): void {
                $establishedState = SnapshotState::make(Established::$name, $record);
                Assert::isInstanceOf($establishedState, SnapshotState::class);

                $snapshotStateTransitionService->transitionToSnapshotState($record, $establishedState);
            })
            ->after(static function (Component $livewire): void {
                $livewire->dispatch(ViewSnapshot::REFRESH_LIVEWIRE_COMPONENT);
            });
    }

    /**
     * Whether established is a state this snapshot may move to, for this user.
     */
    private static function isEstablishable(Snapshot $snapshot): bool
    {
        if (!Authorization::hasPermission(Established::$requiredPermission)) {
            return false;
        }

        // The state machine reports reachable states by name, not by class.
        return in_array(Established::$name, $snapshot->state->transitionableStates(), true);
    }

    /**
     * Approved, and every related entity established as well — what the two steps report
     * in detail. The transition itself stays allowed either way: the check informs the
     * Privacy Officer, it does not overrule them.
     */
    private static function isReadyToEstablish(Snapshot $snapshot): bool
    {
        if (!SnapshotFacade::isApproved($snapshot)) {
            return false;
        }

        return RelatedSnapshotSource::where(['snapshot_id' => $snapshot->id])
            ->whereDoesntHave('snapshotSource', static function (Builder $query): Builder {
                return $query->whereHas('snapshots', static function (Builder $query): Builder {
                    return $query->where(['state' => Established::$name]);
                });
            })->count() === 0;
    }
}
