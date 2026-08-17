<?php

declare(strict_types=1);

use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\Stakeholder;
use App\Models\Wpg\WpgProcessingRecord;
use App\Services\ApReport\AnswerSource;
use App\Services\ApReport\ApAnswer;
use App\Services\ApReport\ApReport;
use App\Services\ApReport\ApReportBuilder;
use Tests\Helpers\Model\OrganisationTestHelper;

function buildApReport(DataBreachRecord $dataBreachRecord): ApReport
{
    return app(ApReportBuilder::class)->build($dataBreachRecord);
}

function apAnswer(ApReport $report, string $number): ?ApAnswer
{
    foreach ($report->answers() as $answer) {
        if ($answer->number === $number) {
            return $answer;
        }
    }

    return null;
}

it('follows the chapter order of the AP notification form', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();

    $report = buildApReport($dataBreachRecord);

    $numbers = array_map(static fn ($chapter): string => $chapter->number, $report->chapters);

    expect($numbers)->toBe(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']);
});

it('marks values held on the data breach record as recorded', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['summary' => 'Een brief is bij de verkeerde ontvanger bezorgd.']);

    $report = buildApReport($dataBreachRecord);
    $answer = apAnswer($report, '5.3');

    expect($answer->source)->toBe(AnswerSource::RECORDED)
        ->and($answer->values)->toBe(['Een brief is bij de verkeerde ontvanger bezorgd.']);
});

it('reports a question the register has no field for as missing', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();

    $report = buildApReport($dataBreachRecord);

    // 6.3.1 asks for the number of affected data records, which the register
    // does not hold anywhere.
    expect(apAnswer($report, '6.3.1')->source)->toBe(AnswerSource::MISSING);
});

it('treats an empty field as missing rather than as an answered question', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()
        ->recycle($organisation)
        ->create(['started_at' => null]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '4.1.1')->source)->toBe(AnswerSource::MISSING);
});

it('derives the special categories from the stakeholders of a linked processing record', function (): void {
    $organisation = OrganisationTestHelper::create();

    $stakeholder = Stakeholder::factory()->recycle($organisation)->create([
        'health' => true,
        'genetic' => false,
        'biometric' => false,
        'race_or_ethnicity' => false,
        'political_attitude' => false,
        'faith_or_belief' => false,
        'sexual_life' => false,
        'trade_association_membership' => false,
    ]);

    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $processingRecord->stakeholders()->attach($stakeholder);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'personal_data_special_categories' => null,
    ]);
    $dataBreachRecord->avgResponsibleProcessingRecords()->attach($processingRecord);

    $report = buildApReport($dataBreachRecord->fresh());
    $answer = apAnswer($report, '6.2');

    expect($answer->source)->toBe(AnswerSource::DERIVED)
        ->and($answer->values)->toBe(['Gegevens over iemands gezondheid'])
        ->and($answer->origins)->toContain($processingRecord->name);
});

it('keeps what was recorded on the breach over what the linked processing suggests', function (): void {
    // The register states what actually leaked; the processing only says what
    // that processing may involve. Over-reporting to the AP is a real cost, so
    // the recorded answer wins.
    $organisation = OrganisationTestHelper::create();

    $stakeholder = Stakeholder::factory()->recycle($organisation)->create(['health' => true]);
    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $processingRecord->stakeholders()->attach($stakeholder);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'personal_data_special_categories' => ['Genetische gegevens'],
    ]);
    $dataBreachRecord->avgResponsibleProcessingRecords()->attach($processingRecord);

    $report = buildApReport($dataBreachRecord->fresh());
    $answer = apAnswer($report, '6.2');

    expect($answer->source)->toBe(AnswerSource::RECORDED)
        ->and($answer->values)->toBe(['Genetische gegevens']);
});

it('derives the legal basis from the register the linked processing sits in', function (): void {
    $organisation = OrganisationTestHelper::create();

    $wpgProcessingRecord = WpgProcessingRecord::factory()->recycle($organisation)->create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();
    $dataBreachRecord->wpgProcessingRecords()->attach($wpgProcessingRecord);

    $report = buildApReport($dataBreachRecord->fresh());
    $answer = apAnswer($report, '1.2');

    expect($answer->source)->toBe(AnswerSource::DERIVED)
        ->and($answer->values)->toBe(['Wet politiegegevens (Wpg)']);
});

it('does not derive an answer when there is no linked content to derive it from', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'personal_data_special_categories' => null,
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '6.2')->source)->toBe(AnswerSource::MISSING)
        ->and(apAnswer($report, '1.2')->source)->toBe(AnswerSource::MISSING);
});

it('counts what still has to be collected and what has to be confirmed', function (): void {
    $organisation = OrganisationTestHelper::create();

    $stakeholder = Stakeholder::factory()->recycle($organisation)->create(['health' => true]);
    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $processingRecord->stakeholders()->attach($stakeholder);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'personal_data_special_categories' => null,
    ]);
    $dataBreachRecord->avgResponsibleProcessingRecords()->attach($processingRecord);

    $report = buildApReport($dataBreachRecord->fresh());

    expect($report->missingCount())->toBeGreaterThan(0)
        ->and($report->needsConfirmationCount())->toBeGreaterThan(0)
        ->and($report->answersNeedingConfirmation())->each->needsConfirmation();
});
