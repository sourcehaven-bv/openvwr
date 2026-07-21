<?php

declare(strict_types=1);

namespace App\Models\States;

use App\Enums\StateColor;
use App\Filament\Actions\DataBreachRecordTransition\DataBreachRecordTransitionAction;
use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecord\Closed;
use App\Models\States\DataBreachRecord\InResponse;
use App\Models\States\DataBreachRecord\NoBreach;
use App\Models\States\DataBreachRecord\Reported;
use App\Models\States\DataBreachRecord\Verified;
use App\Models\States\Transitions\DataBreachRecord\ClosedTransition;
use App\Models\States\Transitions\DataBreachRecord\InResponseTransition;
use App\Models\States\Transitions\DataBreachRecord\NoBreachTransition;
use App\Models\States\Transitions\DataBreachRecord\ReportedTransition;
use App\Models\States\Transitions\DataBreachRecord\VerifiedTransition;
use Spatie\ModelStates\Exceptions\InvalidConfig;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * @extends State<DataBreachRecord>
 */
abstract class DataBreachRecordState extends State
{
    public const DEFAULT_STATE = Reported::class;

    public static StateColor $color = StateColor::GRAY;
    public static string $name = 'none';

    /**
     * Position in the handling workflow, used to tell a step forward from a
     * correction. States outside the linear flow (no breach) share position 0.
     */
    public static int $position = 0;

    /**
     * @return class-string<DataBreachRecordTransitionAction>
     */
    abstract public static function getAction(): string;

    /**
     * @throws InvalidConfig
     */
    public static function config(): StateConfig
    {
        $config = parent::config()
            ->default(self::DEFAULT_STATE)
            ->registerState(Reported::class)
            ->registerState(Verified::class)
            ->registerState(InResponse::class)
            ->registerState(Closed::class)
            ->registerState(NoBreach::class);

        $config->ignoreSameState();

        // Forward through the handling workflow.
        $config->allowTransition(Reported::class, Verified::class, VerifiedTransition::class);
        $config->allowTransition(Verified::class, InResponse::class, InResponseTransition::class);
        $config->allowTransition(InResponse::class, Closed::class, ClosedTransition::class);

        // Verification concluded there was no breach.
        $config->allowTransition(Reported::class, NoBreach::class, NoBreachTransition::class);
        $config->allowTransition(Verified::class, NoBreach::class, NoBreachTransition::class);
        $config->allowTransition(InResponse::class, NoBreach::class, NoBreachTransition::class);

        // Corrections: every step can be walked back, and closed records reopened.
        $config->allowTransition(Verified::class, Reported::class, ReportedTransition::class);
        $config->allowTransition(InResponse::class, Verified::class, VerifiedTransition::class);
        $config->allowTransition(Closed::class, InResponse::class, InResponseTransition::class);
        $config->allowTransition(NoBreach::class, Reported::class, ReportedTransition::class);

        return $config;
    }
}
