<?php

declare(strict_types=1);

namespace App\Models\States\DataBreachRecord;

use App\Enums\StateColor;
use App\Filament\Actions\DataBreachRecordTransition\RespondAction;
use App\Models\States\DataBreachRecordState;

class InResponse extends DataBreachRecordState
{
    public static string $name = 'in_response';
    public static StateColor $color = StateColor::PRIMARY;
    public static int $position = 3;

    public static function getAction(): string
    {
        return RespondAction::class;
    }
}
