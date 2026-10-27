<?php

declare(strict_types=1);

use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Stakeholder;
use App\Models\StakeholderDataItem;
use App\Services\Snapshot\SnapshotSource\AvgResponsibleProcessingRecordPublicDetails;

it('describes a record for the public website', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create([
        'public_summary' => 'Woont u in een straat met betaald parkeren?',
        'has_processors' => true,
        'outside_eu' => true,
        'outside_eu_description' => 'Opslag in de cloud.',
        'outside_eu_protection_level' => false,
        'outside_eu_protection_level_description' => 'Standaardcontractbepalingen.',
        'decision_making' => true,
        'logic' => 'De scanauto vergelijkt kentekens.',
        'importance_consequences' => 'Een naheffing.',
    ]);
    $record->avgGoals()->attach(AvgGoal::factory()->create([
        'goal' => 'Vergunning verlenen',
        'avg_goal_legal_base' => 'Taak van algemeen belang',
    ]));
    $stakeholder = Stakeholder::factory()->create([
        'description' => 'Bewoners',
        'biometric' => false,
        'faith_or_belief' => false,
        'genetic' => false,
        'health' => true,
        'political_attitude' => false,
        'race_or_ethnicity' => false,
        'sexual_life' => false,
        'trade_association_membership' => false,
        'criminal_law' => true,
        'citizen_service_numbers' => true,
    ]);
    $stakeholder->stakeholderDataItems()->attach(StakeholderDataItem::factory()->create([
        'description' => 'Kenteken',
        'collection_purpose' => 'Controle',
        'retention_period' => '2 jaar',
        'source_description' => 'RDW',
        'is_source_stakeholder' => false,
        'is_stakeholder_mandatory' => true,
        'stakeholder_consequences' => 'Geen vergunning',
    ]));
    $stakeholder->stakeholderDataItems()->attach(StakeholderDataItem::factory()->create([
        'description' => 'E-mailadres',
        'collection_purpose' => 'Contact',
        'retention_period' => '2 jaar',
        'source_description' => 'Aanvrager',
        'is_source_stakeholder' => true,
        'is_stakeholder_mandatory' => false,
        'stakeholder_consequences' => 'Wordt niet getoond',
    ]));
    $record->stakeholders()->attach($stakeholder);

    expect((new AvgResponsibleProcessingRecordPublicDetails())->forRecord($record->refresh()))->toBe([
        'summary' => 'Woont u in een straat met betaald parkeren?',
        'goals' => [['goal' => 'Vergunning verlenen', 'legal_basis' => 'public_task']],
        'stakeholders' => [
            [
                'description' => 'Bewoners',
                'special_categories' => ['health', 'criminal_law'],
                'citizen_service_number' => true,
                'data' => [
                    [
                        'description' => 'Kenteken',
                        'purpose' => 'Controle',
                        'retention' => '2 jaar',
                        'source' => 'RDW',
                        'from_stakeholder' => false,
                        'mandatory' => true,
                        'consequences' => 'Geen vergunning',
                    ],
                    [
                        'description' => 'E-mailadres',
                        'purpose' => 'Contact',
                        'retention' => '2 jaar',
                        'source' => 'Aanvrager',
                        'from_stakeholder' => true,
                        'mandatory' => false,
                        'consequences' => null,
                    ],
                ],
            ]],
        'has_processors' => true,
        'outside_eu' => [
            'applies' => true,
            'description' => 'Opslag in de cloud.',
            'adequacy_decision' => false,
            'safeguards' => 'Standaardcontractbepalingen.',
        ],
        'automated_decision_making' => [
            'applies' => true,
            'logic' => 'De scanauto vergelijkt kentekens.',
            'consequences' => 'Een naheffing.',
        ],
    ]);
});

it('leaves out transfer and decision details that do not apply', function (): void {
    $record = AvgResponsibleProcessingRecord::factory()->create([
        'outside_eu' => false,
        'outside_eu_description' => 'Oude toelichting',
        'decision_making' => false,
        'logic' => 'Oude logica',
    ]);

    $details = (new AvgResponsibleProcessingRecordPublicDetails())->forRecord($record);

    expect($details['outside_eu'])->toBe(['applies' => false, 'description' => null, 'adequacy_decision' => null, 'safeguards' => null])
        ->and($details['automated_decision_making'])->toBe(['applies' => false, 'logic' => null, 'consequences' => null]);
});
