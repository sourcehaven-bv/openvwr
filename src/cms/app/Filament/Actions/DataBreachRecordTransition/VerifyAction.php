<?php

declare(strict_types=1);

namespace App\Filament\Actions\DataBreachRecordTransition;

use App\Models\DataBreachRecord;
use App\Models\States\DataBreachRecordState;

class VerifyAction extends DataBreachRecordTransitionAction
{
    public static function makeForDataBreachRecordState(
        DataBreachRecord $dataBreachRecord,
        DataBreachRecordState $dataBreachRecordState,
    ): static {
        return parent::makeForDataBreachRecordState($dataBreachRecord, $dataBreachRecordState);
    }
}
