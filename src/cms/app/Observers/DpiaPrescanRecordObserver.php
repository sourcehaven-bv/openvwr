<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Dpia\DpiaPrescanRecord;
use App\Services\Dpia\PrescanEvaluator;

use function is_array;

class DpiaPrescanRecordObserver
{
    public function __construct(private readonly PrescanEvaluator $prescanEvaluator)
    {
    }

    /**
     * Freezes the DPIA verdict and its reasoning onto the record.
     *
     * The outcome is derived from the answers, so it could be computed on
     * demand -- but storing it keeps a record of what was concluded at the
     * time. The Rijksmodel requires a negative outcome to be documented and
     * archived, and that record should not silently change when the criteria
     * lists are later updated.
     *
     * The invuller can still overwrite the motivation by hand; only an empty
     * one is filled in automatically.
     */
    public function saving(DpiaPrescanRecord $dpiaPrescanRecord): void
    {
        // Recognising an artikel 27 category is what makes it hoog-risico AI,
        // so the boolean follows from the checklist rather than being a second
        // question that could contradict it.
        $categories = $dpiaPrescanRecord->high_risk_ai_categories;
        $dpiaPrescanRecord->high_risk_ai = is_array($categories) && $categories !== [];

        $dpiaPrescanRecord->outcome = $this->prescanEvaluator->dpiaOutcome($dpiaPrescanRecord);

        if ($dpiaPrescanRecord->outcome_motivation !== null && $dpiaPrescanRecord->outcome_motivation !== '') {
            return;
        }

        $dpiaPrescanRecord->outcome_motivation = $this->prescanEvaluator->motivation($dpiaPrescanRecord);
    }
}
