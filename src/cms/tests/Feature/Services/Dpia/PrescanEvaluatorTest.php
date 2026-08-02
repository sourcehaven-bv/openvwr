<?php

declare(strict_types=1);

use App\Enums\Dpia\PrescanOutcome;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Services\Dpia\PrescanCriteria;
use App\Services\Dpia\PrescanEvaluator;
use App\Services\Dpia\PrescanLiveStatus;
use App\Services\Dpia\PrescanOutcomeSummary;
use Tests\Helpers\Dpia\ArrayGet;
use Tests\Helpers\Model\OrganisationTestHelper;

/**
 * An unsaved pre-scan carrying just the answers a test cares about.
 *
 * @param array<string, mixed> $attributes
 */
function prescan(array $attributes = []): DpiaPrescanRecord
{
    return DpiaPrescanRecord::factory()
        ->recycle(OrganisationTestHelper::create())
        ->make($attributes);
}

it('does not require a DPIA when nothing is selected', function (): void {
    $outcome = app(PrescanEvaluator::class)->dpiaOutcome(prescan());

    expect($outcome)->toBe(PrescanOutcome::NOT_REQUIRED);
});

it('requires a DPIA for new legislation', function (): void {
    $outcome = app(PrescanEvaluator::class)->dpiaOutcome(prescan(['new_legislation' => true]));

    expect($outcome)->toBe(PrescanOutcome::REQUIRED);
});

// The AP list is stricter than the EDPB list: one hit is already enough.
it('requires a DPIA for a single AP criterion', function (): void {
    $outcome = app(PrescanEvaluator::class)->dpiaOutcome(prescan(['ap_criteria' => ['cameratoezicht']]));

    expect($outcome)->toBe(PrescanOutcome::REQUIRED);
});

it('only recommends a DPIA for a single EDPB criterion', function (): void {
    $outcome = app(PrescanEvaluator::class)->dpiaOutcome(prescan([
        'edpb_criteria' => ['grootschalige_verwerking'],
    ]));

    expect($outcome)->toBe(PrescanOutcome::RECOMMENDED);
});

it('requires a DPIA for two EDPB criteria', function (): void {
    $outcome = app(PrescanEvaluator::class)->dpiaOutcome(prescan([
        'edpb_criteria' => ['grootschalige_verwerking', 'kwetsbare_personen'],
    ]));

    expect($outcome)->toBe(PrescanOutcome::REQUIRED);
});

it('explains why a DPIA is required', function (): void {
    $motivation = app(PrescanEvaluator::class)->motivation(prescan(['new_legislation' => true]));

    expect($motivation)->toContain('nieuwe wet- of regelgeving');
});

// Also required when no DPIA is needed: the Rijksmodel wants that decision
// written down and archived too.
it('explains why a DPIA is not required', function (): void {
    $motivation = app(PrescanEvaluator::class)->motivation(prescan());

    expect($motivation)->toContain('niet verplicht');
});

it('requires a DTIA for a transfer outside the EEA on standard clauses', function (): void {
    $assessments = app(PrescanEvaluator::class)->evaluate(prescan([
        'international_transfer' => true,
        'outside_eea' => true,
        'transfer_mechanism' => 'scc',
    ]));

    $dtia = collect($assessments)->firstWhere('type', PrescanEvaluator::TYPE_DTIA);

    expect($dtia->outcome)->toBe(PrescanOutcome::REQUIRED);
});

it('does not require a DTIA when an adequacy decision applies', function (): void {
    $assessments = app(PrescanEvaluator::class)->evaluate(prescan([
        'international_transfer' => true,
        'outside_eea' => true,
        'transfer_mechanism' => 'adequaatheidsbesluit',
    ]));

    $dtia = collect($assessments)->firstWhere('type', PrescanEvaluator::TYPE_DTIA);

    expect($dtia->outcome)->toBe(PrescanOutcome::NOT_REQUIRED);
});

it('recommends a KIA for a digital service aimed at minors', function (): void {
    $assessments = app(PrescanEvaluator::class)->evaluate(prescan([
        'digital_service' => true,
        'minors' => true,
    ]));

    $kia = collect($assessments)->firstWhere('type', PrescanEvaluator::TYPE_KIA);

    expect($kia->outcome)->toBe(PrescanOutcome::RECOMMENDED);
});

// An IAMA is not mandatory by law, so it is never "verplicht".
it('recommends but never requires an IAMA for high risk AI', function (): void {
    $assessments = app(PrescanEvaluator::class)->evaluate(prescan([
        'algorithm' => true,
        'high_risk_ai' => true,
    ]));

    $iama = collect($assessments)->firstWhere('type', PrescanEvaluator::TYPE_IAMA);

    expect($iama->outcome)->toBe(PrescanOutcome::RECOMMENDED);
});

it('stores the outcome and its motivation when saving', function (): void {
    $organisation = OrganisationTestHelper::create();

    $record = DpiaPrescanRecord::factory()->recycle($organisation)->create([
        'ap_criteria' => ['cameratoezicht'],
    ]);

    expect($record->outcome)->toBe(PrescanOutcome::REQUIRED)
        ->and($record->outcome_motivation)->toContain('AP-lijst');
});

it('keeps a motivation that was written by hand', function (): void {
    $organisation = OrganisationTestHelper::create();

    $record = DpiaPrescanRecord::factory()->recycle($organisation)->create([
        'ap_criteria' => ['cameratoezicht'],
        'outcome_motivation' => 'Eigen onderbouwing van de privacy officer.',
    ]);

    expect($record->outcome_motivation)->toBe('Eigen onderbouwing van de privacy officer.');
});

// Every criterion shows a short label with the full wording underneath, so the
// list stays scannable without losing the source text.
it('has a label and a description for every criterion', function (): void {
    $apOptions = PrescanCriteria::apOptions();
    $apDescriptions = PrescanCriteria::apDescriptions();
    $edpbOptions = PrescanCriteria::edpbOptions();
    $edpbDescriptions = PrescanCriteria::edpbDescriptions();

    expect($apOptions)->toHaveCount(count(PrescanCriteria::AP))
        ->and($apDescriptions)->toHaveCount(count(PrescanCriteria::AP))
        ->and($edpbOptions)->toHaveCount(count(PrescanCriteria::EDPB))
        ->and($edpbDescriptions)->toHaveCount(count(PrescanCriteria::EDPB));

    // An unresolved translation comes back as the key itself.
    foreach ([...$apOptions, ...$apDescriptions, ...$edpbOptions, ...$edpbDescriptions] as $text) {
        expect($text)->not->toStartWith('dpia_prescan_record.');
    }
});

it('keeps the label short and the description longer', function (): void {
    expect(PrescanCriteria::apOptions()['heimelijk_onderzoek'])
        ->toBe('Heimelijk onderzoek')
        ->and(PrescanCriteria::apDescriptions()['heimelijk_onderzoek'])
        ->toContain('zonder dat de betrokkene');
});

// The counting rules are applied by the form, not left to the reader.
it('shows the AP verdict from the number ticked', function (array $criteria, string $expected): void {
    $record = prescan(['ap_criteria' => $criteria]);
    $status = PrescanLiveStatus::apCriteria(new ArrayGet($record->toArray()));

    expect((string) $status)->toContain($expected);
})->with([
    'none' => [[], 'Nog geen criterium'],
    'one' => [['cameratoezicht'], 'een DPIA is verplicht'],
    'two' => [['cameratoezicht', 'profilering'], 'een DPIA is verplicht'],
]);

it('distinguishes one EDPB criterion from two', function (array $criteria, string $expected): void {
    $record = prescan(['edpb_criteria' => $criteria]);
    $status = PrescanLiveStatus::edpbCriteria(new ArrayGet($record->toArray()));

    expect((string) $status)->toContain($expected);
})->with([
    'none' => [[], 'Nog geen criterium'],
    // One means: assess whether the risk is high, not an automatic yes.
    'one' => [['grootschalige_verwerking'], 'beoordeel of sprake is van een hoog risico'],
    'two' => [['grootschalige_verwerking', 'kwetsbare_personen'], 'een DPIA is verplicht'],
]);

it('says whether the transfer mechanism needs a DTIA', function (?string $mechanism, ?string $expected): void {
    $status = PrescanLiveStatus::transfer(new ArrayGet([
        'international_transfer' => true,
        'outside_eea' => true,
        'transfer_mechanism' => $mechanism,
    ]));

    if ($expected === null) {
        expect($status)->toBeNull();

        return;
    }

    expect((string) $status)->toContain($expected);
})->with([
    'not chosen yet' => [null, 'Kies het doorgiftemechanisme'],
    'adequacy decision' => ['adequaatheidsbesluit', 'geen DTIA nodig'],
    'standard clauses' => ['scc', 'DTIA is verplicht'],
]);

it('says nothing about a DTIA without a transfer outside the EEA', function (): void {
    $status = PrescanLiveStatus::transfer(new ArrayGet([
        'international_transfer' => false,
        'outside_eea' => false,
        'transfer_mechanism' => null,
    ]));

    expect($status)->toBeNull();
});

// "Is dit hoog-risico AI?" is answered by recognising an artikel 27 category,
// not by a separate judgement call that could contradict it.
it('derives high risk AI from the recognised categories', function (): void {
    $organisation = OrganisationTestHelper::create();

    $record = DpiaPrescanRecord::factory()->recycle($organisation)->create([
        'algorithm' => true,
        'high_risk_ai_categories' => ['werkgelegenheid'],
    ]);

    expect($record->high_risk_ai)->toBeTrue();
});

it('does not treat an algorithm without a category as high risk', function (): void {
    $organisation = OrganisationTestHelper::create();

    $record = DpiaPrescanRecord::factory()->recycle($organisation)->create([
        'algorithm' => true,
        'high_risk_ai_categories' => [],
    ]);

    expect($record->high_risk_ai)->toBeFalse();
});

it('has a label and description for every artikel 27 category', function (): void {
    $options = PrescanCriteria::highRiskAiOptions();
    $descriptions = PrescanCriteria::highRiskAiDescriptions();

    expect($options)->toHaveCount(count(PrescanCriteria::HIGH_RISK_AI))
        ->and($descriptions)->toHaveCount(count(PrescanCriteria::HIGH_RISK_AI));

    foreach ([...$options, ...$descriptions] as $text) {
        expect($text)->not->toStartWith('dpia_prescan_record.');
    }
});

it('reports which artikel 27 categories were recognised', function (): void {
    $status = PrescanLiveStatus::highRiskAi(new ArrayGet([
        'algorithm' => true,
        'high_risk_ai_categories' => ['werkgelegenheid', 'onderwijs'],
    ]));

    expect((string) $status)->toContain('2 categorieën herkend');
});

it('says nothing about artikel 27 when no algorithm is used', function (): void {
    $status = PrescanLiveStatus::highRiskAi(new ArrayGet(['algorithm' => false]));

    expect($status)->toBeNull();
});

// Each trigger is named separately in the motivation, so the reader can see
// which answer carried the outcome rather than just that something did.
it('names departmental policy as a reason', function (): void {
    $motivation = app(PrescanEvaluator::class)->motivation(prescan(['departmental_policy' => true]));

    expect($motivation)->toContain(__('dpia_prescan_record.reason_departmental_policy'));
});

it('names the public cloud as a reason', function (): void {
    $motivation = app(PrescanEvaluator::class)->motivation(prescan(['public_cloud' => true]));

    expect($motivation)->toContain(__('dpia_prescan_record.reason_public_cloud'));
});

// More than one reason is written as a list, so the last one is joined with
// "en" instead of a comma.
it('joins several reasons into one sentence', function (): void {
    $motivation = app(PrescanEvaluator::class)->motivation(prescan([
        'new_legislation' => true,
        'departmental_policy' => true,
        'public_cloud' => true,
    ]));

    expect($motivation)
        ->toContain(__('dpia_prescan_record.reason_new_legislation'))
        ->toContain(__('dpia_prescan_record.reason_public_cloud'))
        ->toContain(' ' . __('general.and') . ' ');
});

it('ignores criteria that are not usable strings', function (): void {
    $outcome = app(PrescanEvaluator::class)->dpiaOutcome(prescan([
        'ap_criteria' => ['', 'cameratoezicht'],
        'edpb_criteria' => 'geen array',
    ]));

    // Only the one real criterion counts, and one AP criterion is decisive.
    expect($outcome)->toBe(PrescanOutcome::REQUIRED);
});

// A transfer outside the EEA without a chosen mechanism cannot be judged yet,
// so the DTIA is advised rather than settled either way.
it('recommends a DTIA when the transfer mechanism is still unknown', function (): void {
    $assessments = app(PrescanEvaluator::class)->evaluate(prescan([
        'international_transfer' => true,
        'outside_eea' => true,
        'transfer_mechanism' => null,
    ]));

    $dtia = collect($assessments)->firstWhere('type', PrescanEvaluator::TYPE_DTIA);

    expect($dtia->outcome)->toBe(PrescanOutcome::RECOMMENDED)
        ->and($dtia->isAdvised())->toBeTrue()
        ->and($dtia->isRequired())->toBeFalse()
        ->and($dtia->label())->not->toBe('');
});

// The outcome block on the form: one row per instrument, so the invuller sees
// the DPIA, DTIA, KIA and IAMA conclusions side by side.
it('summarises every instrument on the form', function (): void {
    $summary = PrescanOutcomeSummary::render(new ArrayGet([
        'ap_criteria' => ['cameratoezicht'],
    ]))->toHtml();

    expect($summary)
        ->toContain(__('dpia_prescan_record.outcome_verplicht'))
        ->toContain(__('dpia_prescan_record.assessment_dpia'));
});

// An algorithm brings the IAMA into view, so the block explains it rather than
// leaving the reader to guess why a fourth instrument appeared.
it('adds the IAMA note when an algorithm is used', function (): void {
    $summary = PrescanOutcomeSummary::render(new ArrayGet(['algorithm' => true]))->toHtml();

    expect($summary)->toContain(__('dpia_prescan_record.iama_note'));
});

it('leaves the IAMA note out when no algorithm is used', function (): void {
    $summary = PrescanOutcomeSummary::render(new ArrayGet(['algorithm' => false]))->toHtml();

    expect($summary)->not->toContain(__('dpia_prescan_record.iama_note'));
});

it('ignores criteria in the form state that are not usable strings', function (): void {
    $summary = PrescanOutcomeSummary::render(new ArrayGet([
        'ap_criteria' => ['', 'cameratoezicht', 123],
        'edpb_criteria' => 'geen array',
    ]))->toHtml();

    expect($summary)->toContain(__('dpia_prescan_record.outcome_verplicht'));
});

it('says so when an algorithm is used but no artikel 27 category fits', function (): void {
    $status = PrescanLiveStatus::highRiskAi(new ArrayGet([
        'algorithm' => true,
        'high_risk_ai_categories' => [],
    ]));

    expect((string) $status)->toContain(__('dpia_prescan_record.status_high_risk_ai_none'));
});

it('ignores artikel 27 categories that are not a list', function (): void {
    $status = PrescanLiveStatus::highRiskAi(new ArrayGet([
        'algorithm' => true,
        'high_risk_ai_categories' => 'geen array',
    ]));

    expect((string) $status)->toContain(__('dpia_prescan_record.status_high_risk_ai_none'));
});
