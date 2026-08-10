<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Stakeholder;

use function __;

class AvgProcessorProcessingRecordObserver
{
    public function saving(AvgProcessorProcessingRecord $avgProcessorProcessingRecord): void
    {
        $this->resetProcessors($avgProcessorProcessingRecord);
        $this->resetProcessingGoal($avgProcessorProcessingRecord);
        $this->resetInvolvedData($avgProcessorProcessingRecord);
        $this->resetDecisionMaking($avgProcessorProcessingRecord);
        $this->resetSystem($avgProcessorProcessingRecord);
        $this->resetAlgorithms($avgProcessorProcessingRecord);
        $this->resetSecurity($avgProcessorProcessingRecord);
        $this->resetPassthrough($avgProcessorProcessingRecord);
    }

    private function resetProcessors(AvgProcessorProcessingRecord $avgProcessorProcessingRecord): void
    {
        if ($avgProcessorProcessingRecord->has_processors === false) {
            $avgProcessorProcessingRecord->processors()->delete();
        }
    }

    private function resetProcessingGoal(AvgProcessorProcessingRecord $avgProcessorProcessingRecord): void
    {
        if ($avgProcessorProcessingRecord->has_goal === false) {
            $avgProcessorProcessingRecord->avgGoals()->each(static function (AvgGoal $avgGoal): void {
                $avgGoal->delete();
            });
        }
    }

    private function resetInvolvedData(AvgProcessorProcessingRecord $avgProcessorProcessingRecord): void
    {
        if ($avgProcessorProcessingRecord->has_involved === false) {
            $avgProcessorProcessingRecord->stakeholders()->each(static function (Stakeholder $stakeholder): void {
                $stakeholder->delete();
            });
        }
    }

    private function resetDecisionMaking(AvgProcessorProcessingRecord $avgProcessorProcessingRecord): void
    {
        if ($avgProcessorProcessingRecord->decision_making === false) {
            $avgProcessorProcessingRecord->logic = '';
            $avgProcessorProcessingRecord->importance_consequences = '';
        }
    }

    /**
     * Detach only the pivot rows: the algorithm records themselves are
     * shared registrations that outlive any single processing.
     */
    private function resetAlgorithms(AvgProcessorProcessingRecord $avgProcessorProcessingRecord): void
    {
        if ($avgProcessorProcessingRecord->has_algorithms === false) {
            $avgProcessorProcessingRecord->algorithmRecords()->detach();
        }
    }

    private function resetSystem(AvgProcessorProcessingRecord $avgProcessorProcessingRecord): void
    {
        if ($avgProcessorProcessingRecord->has_systems === false) {
            $avgProcessorProcessingRecord->systems()->delete();
        }
    }

    private function resetSecurity(AvgProcessorProcessingRecord $avgProcessorProcessingRecord): void
    {
        if ($avgProcessorProcessingRecord->has_security === false) {
            $avgProcessorProcessingRecord->measures_implemented = false;
            $avgProcessorProcessingRecord->other_measures = false;
            $avgProcessorProcessingRecord->measures_description = null;

            $avgProcessorProcessingRecord->has_pseudonymization = false;
        }

        if ($avgProcessorProcessingRecord->has_pseudonymization === false) {
            $avgProcessorProcessingRecord->pseudonymization = '';
        }
    }

    private function resetPassthrough(AvgProcessorProcessingRecord $avgProcessorProcessingRecord): void
    {
        if ($avgProcessorProcessingRecord->outside_eu === false) {
            $avgProcessorProcessingRecord->country = null;
            $avgProcessorProcessingRecord->outside_eu_protection_level = false;
            $avgProcessorProcessingRecord->outside_eu_description = null;
            $avgProcessorProcessingRecord->outside_eu_protection_level_description = '';
        }

        if ($avgProcessorProcessingRecord->country !== __('general.country_other')) {
            $avgProcessorProcessingRecord->country_other = null;
        }
    }
}
