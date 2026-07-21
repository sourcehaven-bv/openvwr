<?php

declare(strict_types=1);

namespace App\Models\States\DataBreachRecord;

use App\Enums\StateColor;
use App\Filament\Actions\DataBreachRecordTransition\CloseAction;
use App\Models\States\DataBreachRecordState;

class Closed extends DataBreachRecordState
{
    public static string $name = 'closed';
    public static StateColor $color = StateColor::SUCCESS;
    public static int $position = 4;

    public static function getAction(): string
    {
        return CloseAction::class;
    }
}
