<?php

declare(strict_types=1);

namespace App\Models\States;

use App\Enums\Authorization\Permission;
use App\Enums\StateColor;
use App\Filament\Actions\SnapshotTransition\SnapshotTransitionAction;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use App\Models\States\Transitions\ApprovedTransition;
use App\Models\States\Transitions\EstablishedTransition;
use App\Models\States\Transitions\ObsoleteTransition;
use Spatie\ModelStates\Exceptions\InvalidConfig;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

use function usort;

use const PHP_INT_MAX;

/**
 * @extends State<Snapshot>
 */
abstract class SnapshotState extends State
{
    public const DEFAULT_STATE = InReview::class;
    public const OBSOLETE_STATE = Obsolete::class;

    /**
     * The forward "happy path", in display order. Used to order the transition
     * menu and the status-flow diagram consistently. Obsolete is not on it: it is
     * a branch shown after the line, from any non-obsolete state.
     */
    public const FORWARD_LINE = [
        InReview::class,
        Approved::class,
        Established::class,
    ];

    public static StateColor $color = StateColor::GRAY;
    public static string $name = 'none';
    public static Permission $requiredPermission = Permission::SNAPSHOT_CREATE;

    /**
     * @return class-string<SnapshotTransitionAction>
     */
    abstract public static function getAction(): string;

    /**
     * The states this snapshot may transition to, ordered for the transition
     * menu: forward along FORWARD_LINE, then obsolete. Reachability comes from the
     * state machine itself (transitionableStates() honours the allowTransition
     * edges and their guards); this only imposes a stable display order.
     *
     * @return array<int, string>
     */
    public function orderedTransitionableStates(): array
    {
        /** @var array<int, string> $states */
        $states = $this->transitionableStates();

        usort($states, static function (string $a, string $b): int {
            return self::lineOrder($a) <=> self::lineOrder($b);
        });

        return $states;
    }

    /**
     * Display rank of a state: its position on FORWARD_LINE, with anything off
     * the line (obsolete) sorted last.
     */
    private static function lineOrder(string $stateName): int
    {
        foreach (self::FORWARD_LINE as $index => $stateClass) {
            if ($stateClass::$name === $stateName) {
                return $index;
            }
        }

        return PHP_INT_MAX;
    }

    /**
     * @throws InvalidConfig
     */
    public static function config(): StateConfig
    {
        $config = parent::config()
            ->default(self::DEFAULT_STATE)
            ->registerState(InReview::class)
            ->registerState(Approved::class)
            ->registerState(Established::class)
            ->registerState(Obsolete::class);

        $config->ignoreSameState();
        $config->allowTransition(InReview::class, Approved::class, ApprovedTransition::class);
        $config->allowTransition(Approved::class, Established::class, EstablishedTransition::class);
        // Direct skip: an authorised user may establish straight from review,
        // bypassing approval. It only sets established_at (via EstablishedTransition)
        // and deliberately does not notify approvers or record an approved step.
        $config->allowTransition(InReview::class, Established::class, EstablishedTransition::class);
        $config->allowTransition(InReview::class, Obsolete::class, ObsoleteTransition::class);
        $config->allowTransition(Approved::class, Obsolete::class, ObsoleteTransition::class);
        $config->allowTransition(Established::class, Obsolete::class, ObsoleteTransition::class);

        return $config;
    }
}
