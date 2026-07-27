<?php

declare(strict_types=1);

namespace App\Models\States\DataBreachRecord;

use App\Enums\StateColor;
use App\Filament\Actions\DataBreachRecordTransition\MarkAsNoBreachAction;
use App\Models\States\DataBreachRecordState;

class NoBreach extends DataBreachRecordState
{
    public static string $name = 'no_breach';
    public static StateColor $color = StateColor::GRAY;

    public static function getAction(): string
    {
        return MarkAsNoBreachAction::class;
    }
}
