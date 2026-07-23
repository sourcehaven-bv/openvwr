<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Models\Snapshot;
use App\Models\SnapshotTransition;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
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
     * The main line, in order. Obsolete is deliberately not on it: it is a
     * branch drawn off to the side only when the snapshot actually reached it.
     */
    private const MAIN_LINE = [
        InReview::class,
        Approved::class,
        Established::class,
    ];

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
    private static function buildFlow(Snapshot $snapshot): array
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

        $stations = [];
        foreach (self::MAIN_LINE as $index => $stateClass) {
            $stateName = $stateClass::$name;
            $transition = $reachedAt[$stateName] ?? null;
            // A snapshot always starts in review even before any transition is
            // recorded, so the first station counts as reached by default.
            $reached = $transition !== null || $index === 0;

            $stations[] = self::station($stateClass, $reached, $currentState === $stateName, $transition);
        }

        $obsolete = null;
        if ($currentState === Obsolete::$name) {
            $obsolete = self::station(Obsolete::class, true, true, $reachedAt[Obsolete::$name] ?? null);
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
        ?SnapshotTransition $transition,
    ): array {
        return [
            'label' => __(sprintf('snapshot_state.label.%s', $stateClass::$name)),
            'color' => $stateClass::$color->value,
            'reached' => $reached,
            'current' => $current,
            'reached_at' => $transition?->created_at,
            'reached_by' => $transition?->creator?->name,
        ];
    }
}
