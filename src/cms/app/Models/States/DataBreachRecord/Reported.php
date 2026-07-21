<?php

declare(strict_types=1);

namespace App\Models\States\DataBreachRecord;

use App\Enums\StateColor;
use App\Filament\Actions\DataBreachRecordTransition\ReportAction;
use App\Models\States\DataBreachRecordState;

class Reported extends DataBreachRecordState
{
    public static string $name = 'reported';
    public static StateColor $color = StateColor::INFO;

    public static function getAction(): string
    {
        return ReportAction::class;
    }
}
