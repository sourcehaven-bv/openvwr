<?php

declare(strict_types=1);

namespace App\Observers;

use App\Filament\Forms\GebDpiaQuestionnaire;
use App\Models\Avg\AvgResponsibleProcessingRecord;

use function __;

class AvgResponsibleProcessingRecordObserver
{
    public function saving(AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord): void
    {
        $this->resetProcessors($avgResponsibleProcessingRecord);
        $this->resetDecisionMaking($avgResponsibleProcessingRecord);
        $this->resetSystems($avgResponsibleProcessingRecord);
        $this->resetSecurity($avgResponsibleProcessingRecord);
        $this->resetPassthrough($avgResponsibleProcessingRecord);
        $this->resetGebDpia($avgResponsibleProcessingRecord);
    }

    /**
     * The GEB (DPIA) pre-screen is a progressive OR-questionnaire: once a GEB
     * was executed, or once an earlier criterion is answered "ja", the later
     * questions are never asked. Reset those unreached answers so the stored
     * data matches what the questionnaire actually presented.
     */
    private function resetGebDpia(AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord): void
    {
        $answers = [];
        foreach (GebDpiaQuestionnaire::CRITERIA as $criterion) {
            $answers[$criterion] = $avgResponsibleProcessingRecord->getAttribute($criterion) === true;
        }

        $reset = GebDpiaQuestionnaire::resetUnreached($avgResponsibleProcessingRecord->geb_dpia_executed === true, $answers);

        foreach ($reset as $criterion => $value) {
            $avgResponsibleProcessingRecord->setAttribute($criterion, $value);
        }
    }

    private function resetProcessors(AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord): void
    {
        if ($avgResponsibleProcessingRecord->has_processors === false) {
            $avgResponsibleProcessingRecord->processors()->delete();
        }
    }

    private function resetDecisionMaking(AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord): void
    {
        if ($avgResponsibleProcessingRecord->decision_making === false) {
            $avgResponsibleProcessingRecord->logic = null;
            $avgResponsibleProcessingRecord->importance_consequences = null;
        }
    }

    private function resetSystems(AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord): void
    {
        if ($avgResponsibleProcessingRecord->has_systems === false) {
            $avgResponsibleProcessingRecord->systems()->delete();
        }
    }

    private function resetSecurity(AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord): void
    {
        if ($avgResponsibleProcessingRecord->has_security === false) {
            $avgResponsibleProcessingRecord->measures_implemented = false;
            $avgResponsibleProcessingRecord->other_measures = false;
            $avgResponsibleProcessingRecord->measures_description = null;

            $avgResponsibleProcessingRecord->has_pseudonymization = false;
        }

        if ($avgResponsibleProcessingRecord->has_pseudonymization === false) {
            $avgResponsibleProcessingRecord->pseudonymization = null;
        }
    }

    private function resetPassthrough(AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord): void
    {
        if ($avgResponsibleProcessingRecord->outside_eu === false) {
            $avgResponsibleProcessingRecord->country = null;
            $avgResponsibleProcessingRecord->outside_eu_protection_level = false;
            $avgResponsibleProcessingRecord->outside_eu_description = null;
            $avgResponsibleProcessingRecord->outside_eu_protection_level_description = null;
        }

        if ($avgResponsibleProcessingRecord->country !== __('general.country_other')) {
            $avgResponsibleProcessingRecord->country_other = null;
        }

        if ($avgResponsibleProcessingRecord->outside_eu_protection_level === true) {
            $avgResponsibleProcessingRecord->outside_eu_protection_level_description = null;
        }
    }
}
