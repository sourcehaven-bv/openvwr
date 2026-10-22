<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Wpg\WpgProcessingRecord;
use App\Observers\Concerns\DetachesSharedRecords;

class WpgProcessingRecordObserver
{
    use DetachesSharedRecords;

    public function saving(WpgProcessingRecord $wpgProcessingRecord): void
    {
        $this->resetProcessors($wpgProcessingRecord);
        $this->resetReceivers($wpgProcessingRecord);
        $this->resetDecisionMaking($wpgProcessingRecord);
        $this->resetSystems($wpgProcessingRecord);
        $this->resetAlgorithms($wpgProcessingRecord);
        $this->resetSecurity($wpgProcessingRecord);
        $this->resetCategoriesInvolved($wpgProcessingRecord);
    }

    private function resetProcessors(WpgProcessingRecord $wpgProcessingRecord): void
    {
        if ($wpgProcessingRecord->has_processors === false) {
            $this->detachShared($wpgProcessingRecord, $wpgProcessingRecord->processors());
        }
    }

    private function resetReceivers(WpgProcessingRecord $wpgProcessingRecord): void
    {
        if ($wpgProcessingRecord->article_17_a === false) {
            $wpgProcessingRecord->explanation_transfer = null;
        }
    }

    private function resetDecisionMaking(WpgProcessingRecord $wpgProcessingRecord): void
    {
        if ($wpgProcessingRecord->decision_making === false) {
            $wpgProcessingRecord->logic = null;
            $wpgProcessingRecord->consequences = null;
        }
    }

    private function resetAlgorithms(WpgProcessingRecord $wpgProcessingRecord): void
    {
        if ($wpgProcessingRecord->has_algorithms === false) {
            $this->detachShared($wpgProcessingRecord, $wpgProcessingRecord->algorithmRecords());
        }
    }

    private function resetSystems(WpgProcessingRecord $wpgProcessingRecord): void
    {
        if ($wpgProcessingRecord->has_systems === false) {
            $this->detachShared($wpgProcessingRecord, $wpgProcessingRecord->systems());
        }
    }

    private function resetSecurity(WpgProcessingRecord $wpgProcessingRecord): void
    {
        if ($wpgProcessingRecord->has_security === false) {
            $wpgProcessingRecord->measures_implemented = false;
            $wpgProcessingRecord->other_measures = false;
            $wpgProcessingRecord->measures_description = null;

            $wpgProcessingRecord->has_pseudonymization = false;
        }

        if ($wpgProcessingRecord->has_pseudonymization === false) {
            $wpgProcessingRecord->pseudonymization = null;
        }
    }

    private function resetCategoriesInvolved(WpgProcessingRecord $wpgProcessingRecord): void
    {
        if ($wpgProcessingRecord->third_parties === false) {
            $wpgProcessingRecord->third_party_explanation = null;
        }
    }
}
