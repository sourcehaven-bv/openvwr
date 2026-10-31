<?php

declare(strict_types=1);

namespace App\Services\Snapshot\SnapshotSource;

use App\Enums\AvgLegalBasis;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Stakeholder;
use App\Models\StakeholderDataItem;

use function array_filter;
use function array_keys;

/**
 * The public fields of a processing record as structured data, next to the
 * rendered markdown. A theme can then explain the record in its own words and
 * order, for example as questions a resident would ask, instead of showing the
 * generic text.
 *
 * Only fields that article 13 and 14 AVG require towards residents are here;
 * internal fields such as security measures or the DPIA stay private. The
 * receivers have their own approval, so the publication adds those from their
 * established snapshots, like the markdown does.
 */
class AvgResponsibleProcessingRecordPublicDetails
{
    private const array SPECIAL_CATEGORIES = [
        'race_or_ethnicity',
        'political_attitude',
        'faith_or_belief',
        'trade_association_membership',
        'genetic',
        'biometric',
        'health',
        'sexual_life',
        'criminal_law',
    ];

    /**
     * @return array<string, mixed>
     */
    public function forRecord(AvgResponsibleProcessingRecord $record): array
    {
        return [
            'summary' => $record->public_summary,
            'goals' => $record->avgGoals->map(static fn (AvgGoal $goal): array => [
                'goal' => $goal->goal,
                'legal_basis' => AvgLegalBasis::fromLabel($goal->avg_goal_legal_base)?->value,
            ])->values()->all(),
            'stakeholders' => $record->stakeholders->map(
                fn (Stakeholder $stakeholder): array => $this->stakeholder($stakeholder),
            )->values()->all(),
            'has_processors' => $record->has_processors,
            'outside_eu' => [
                'applies' => $record->outside_eu,
                'description' => $record->outside_eu ? $record->outside_eu_description : null,
                'adequacy_decision' => $record->outside_eu ? $record->outside_eu_protection_level : null,
                'safeguards' => $record->outside_eu ? $record->outside_eu_protection_level_description : null,
            ],
            'automated_decision_making' => [
                'applies' => $record->decision_making,
                'logic' => $record->decision_making ? $record->logic : null,
                'consequences' => $record->decision_making ? $record->importance_consequences : null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function stakeholder(Stakeholder $stakeholder): array
    {
        $flags = [];
        foreach (self::SPECIAL_CATEGORIES as $category) {
            $flags[$category] = $stakeholder->{$category};
        }

        return [
            'description' => $stakeholder->description,
            'special_categories' => array_keys(array_filter($flags)),
            'citizen_service_number' => $stakeholder->citizen_service_numbers,
            'data' => $stakeholder->stakeholderDataItems->map(static fn (StakeholderDataItem $item): array => [
                'description' => $item->description,
                'purpose' => $item->collection_purpose,
                'retention' => $item->retention_period,
                'source' => $item->source_description,
                'from_stakeholder' => $item->is_source_stakeholder,
                'mandatory' => $item->is_stakeholder_mandatory,
                'consequences' => $item->is_stakeholder_mandatory ? $item->stakeholder_consequences : null,
            ])->values()->all(),
        ];
    }
}
