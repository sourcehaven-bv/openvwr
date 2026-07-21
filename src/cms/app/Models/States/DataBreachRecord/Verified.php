<?php

declare(strict_types=1);

namespace App\Models\States\DataBreachRecord;

use App\Enums\StateColor;
use App\Filament\Actions\DataBreachRecordTransition\VerifyAction;
use App\Models\States\DataBreachRecordState;

class Verified extends DataBreachRecordState
{
    public static string $name = 'verified';
    public static StateColor $color = StateColor::WARNING;

    public static function getAction(): string
    {
        return VerifyAction::class;
    }
}
