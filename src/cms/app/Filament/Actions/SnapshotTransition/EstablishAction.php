<?php

declare(strict_types=1);

namespace App\Filament\Actions\SnapshotTransition;

use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\View;
use Filament\Support\Enums\Width;
use App\Facades\Snapshot as SnapshotFacade;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use App\Models\States\SnapshotState;
use Illuminate\Database\Eloquent\Builder;

use function __;

class EstablishAction extends SnapshotTransitionAction
{
    public static function makeForSnapshotState(Snapshot $snapshot, SnapshotState $snapshotState): static
    {
        return parent::makeForSnapshotState($snapshot, $snapshotState)
            ->color(static function () use ($snapshot): string {
                $isApproved = SnapshotFacade::isApproved($snapshot);
                $hasAllEstablishedRelatedSnapshotSources = RelatedSnapshotSource::where(['snapshot_id' => $snapshot->id])
                    ->whereDoesntHave('snapshotSource', static function (Builder $query): Builder {
                        return $query->whereHas('snapshots', static function (Builder $query): Builder {
                            return $query->where(['state' => Established::$name]);
                        });
                    })->count() === 0;

                return $isApproved && $hasAllEstablishedRelatedSnapshotSources ? 'success' : 'warning';
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
            ->modalWidth(Width::FiveExtraLarge);
    }
}
