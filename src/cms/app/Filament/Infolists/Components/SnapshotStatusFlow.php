<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Models\Snapshot;
use App\Models\SnapshotTransition;
use App\Models\States\Snapshot\Obsolete;
use App\Models\States\SnapshotState;
use Filament\Infolists\Components\ViewEntry;

use function __;
use function array_key_exists;
use function sprintf;

/**
 * Metro-line style status flow for a snapshot: the happy path
 * (In review -> Goedgekeurd -> Vastgesteld) as a line of stations, with
 * Vervallen as a branch reachable from any state. Each reached station shows
 * when it was reached and by whom, taken from the recorded state transitions.
 */
class SnapshotStatusFlow extends ViewEntry
{
    /**
     * The main line, in order: the shared forward happy path from the state
     * model. Obsolete is deliberately not on it — it is a branch drawn off to the
     * side only when the snapshot actually reached it.
     */
    private const MAIN_LINE = SnapshotState::FORWARD_LINE;

    public static function make(string $name = 'status_flow'): static
    {
        return parent::make($name)
            ->hiddenLabel()
            ->view('filament.infolists.components.entries.snapshot_status_flow')
            ->state(static fn (Snapshot $snapshot): array => self::buildFlow($snapshot));
    }

    /**
     * @return array{stations: list<array<string, mixed>>, obsolete: array<string, mixed>|null}
     */
    public static function buildFlow(Snapshot $snapshot): array
    {
        // The first time each state was reached, keyed by state name.
        /** @var array<string, SnapshotTransition> $reachedAt */
        $reachedAt = [];
        foreach ($snapshot->snapshotTransitions as $transition) {
            $stateName = $transition->state::$name;
            if (!array_key_exists($stateName, $reachedAt)) {
                $reachedAt[$stateName] = $transition;
            }
        }

        $currentState = $snapshot->state::$name;

        // The furthest position the line has progressed to. The current state is
        // always treated as reached (see below), so when it is on the line it is
        // the furthest point; otherwise fall back to the last recorded station.
        $furthestReachedIndex = 0;
        foreach (self::MAIN_LINE as $index => $stateClass) {
            $stateName = $stateClass::$name;
            if ($currentState === $stateName || array_key_exists($stateName, $reachedAt)) {
                $furthestReachedIndex = $index;
            }
        }

        $stations = [];
        foreach (self::MAIN_LINE as $index => $stateClass) {
            $stateName = $stateClass::$name;
            $transition = $reachedAt[$stateName] ?? null;
            $isCurrent = $currentState === $stateName;
            // A snapshot always starts in review even before any transition is
            // recorded, so the first station counts as reached by default. The
            // current station is always reached too: a state can be set directly
            // (e.g. seeded data) without a recorded transition.
            $reached = $transition !== null || $index === 0 || $isCurrent;
            // A station the line moved past but never reached was skipped (e.g.
            // established straight from review), as opposed to one not reached yet.
            $skipped = !$reached && $index < $furthestReachedIndex;

            $stations[] = self::station($stateClass, $reached, $isCurrent, $skipped, $transition);
        }

        $obsolete = null;
        if ($currentState === Obsolete::$name) {
            $obsolete = self::station(Obsolete::class, true, true, false, $reachedAt[Obsolete::$name] ?? null);
        }

        return ['stations' => $stations, 'obsolete' => $obsolete];
    }

    /**
     * @param class-string<SnapshotState> $stateClass
     *
     * @return array<string, mixed>
     */
    private static function station(
        string $stateClass,
        bool $reached,
        bool $current,
        bool $skipped,
        ?SnapshotTransition $transition,
    ): array {
        return [
            'label' => __(sprintf('snapshot_state.label.%s', $stateClass::$name)),
            'color' => $stateClass::$color->value,
            'reached' => $reached,
            'current' => $current,
            'skipped' => $skipped,
            'reached_at' => $transition?->created_at,
            'reached_by' => $transition?->creator?->name,
        ];
    }
}
