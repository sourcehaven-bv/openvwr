<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
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

it('points at the special categories of the linked processing without answering for the breach', function (): void {
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

    // The breach record has a field for this, so the processing only supplies a
    // pointer: the officer records what actually leaked instead of inheriting
    // everything the processing might involve.
    expect($answer->source)->toBe(AnswerSource::MISSING)
        ->and($answer->values)->toBe([])
        ->and($answer->hints)->toBe(['Gegevens over iemands gezondheid'])
        ->and($answer->origins)->toContain($processingRecord->name);
});

it('keeps what was recorded on the breach and drops the pointer', function (): void {
    // Once the officer has recorded what leaked, the processing has nothing left
    // to add: the recorded value stands on its own.
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
        ->and($answer->values)->toBe(['Genetische gegevens'])
        ->and($answer->hints)->toBe([]);
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

it('spells out an "other" choice with the text that was typed in', function (): void {
    // The AP form asks for the free-text explanation next to the "other" option;
    // presenting them apart would make the officer hunt for the pair.
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'nature_of_incident' => 'Overig',
        'nature_of_incident_other' => 'Papieren dossier in de trein laten liggen',
        'personal_data_categories' => ['Naam'],
        'personal_data_categories_other' => 'Personeelsnummer',
        'reported_to_involved' => true,
        'reported_to_involved_communication' => ['Per brief'],
        'reported_to_involved_communication_other' => 'Via de huisarts',
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '5.2')->values)
        ->toBe(['Overig, namelijk: Papieren dossier in de trein laten liggen'])
        ->and(apAnswer($report, '6.1')->values)
        ->toBe(['Naam', 'Anders, namelijk: Personeelsnummer'])
        ->and(apAnswer($report, '10.1.7')->values)
        ->toBe(['Per brief', 'Anders, namelijk: Via de huisarts']);
});

it('derives the AVG as legal basis from a linked AVG processing record', function (): void {
    $organisation = OrganisationTestHelper::create();

    $responsibleRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $processorRecord = AvgProcessorProcessingRecord::factory()->recycle($organisation)->create();

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();
    $dataBreachRecord->avgResponsibleProcessingRecords()->attach($responsibleRecord);
    $dataBreachRecord->avgProcessorProcessingRecords()->attach($processorRecord);

    $answer = apAnswer(buildApReport($dataBreachRecord->fresh()), '1.2');

    // Both registers point at the same statute, so it is listed once.
    expect($answer->values)->toBe(['Algemene verordening gegevensbescherming (AVG)'])
        ->and($answer->origins)->toContain($responsibleRecord->name)
        ->and($answer->origins)->toContain($processorRecord->name);
});

it('lists the responsible and its address as recorded on the breach', function (): void {
    $organisation = OrganisationTestHelper::create();

    $responsible = Responsible::factory()->recycle($organisation)->create(['name' => 'Afdeling Zorg']);
    Address::factory()->create([
        'addressable_id' => $responsible->id,
        'addressable_type' => Responsible::class,
        'address' => 'Stationsplein 1',
        'postal_code' => '1012 AB',
        'city' => 'Amsterdam',
    ]);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();
    $dataBreachRecord->responsibles()->attach($responsible);

    $report = buildApReport($dataBreachRecord->fresh());

    expect(apAnswer($report, '3.1.1b')->values)->toBe(['Afdeling Zorg'])
        ->and(apAnswer($report, '3.1.1c')->values)->toBe(['Stationsplein 1, 1012 AB Amsterdam']);
});

it('leaves the address empty when the responsible has none', function (): void {
    $organisation = OrganisationTestHelper::create();

    $responsible = Responsible::factory()->recycle($organisation)->create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();
    $dataBreachRecord->responsibles()->attach($responsible);

    $report = buildApReport($dataBreachRecord->fresh());

    expect(apAnswer($report, '3.1.1b')->source)->toBe(AnswerSource::RECORDED)
        ->and(apAnswer($report, '3.1.1c')->source)->toBe(AnswerSource::MISSING);
});

it('lists the linked documents as supporting documentation', function (): void {
    $organisation = OrganisationTestHelper::create();

    $document = Document::factory()->recycle($organisation)->create(['name' => 'Onderzoeksrapport']);
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();
    $dataBreachRecord->documents()->attach($document);

    expect(apAnswer(buildApReport($dataBreachRecord->fresh()), '5.4')->values)
        ->toBe(['Onderzoeksrapport']);
});

it('names the processors and receivers of the linked processing as involved organisations', function (): void {
    $organisation = OrganisationTestHelper::create();

    $processor = Processor::factory()->recycle($organisation)->create(['name' => 'Drukkerij Van Dijk']);
    $receiver = Receiver::factory()->recycle($organisation)->create(['description' => 'Zorgverzekeraar']);

    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $processingRecord->processors()->attach($processor);
    $processingRecord->receivers()->attach($receiver);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();
    $dataBreachRecord->avgResponsibleProcessingRecords()->attach($processingRecord);

    expect(apAnswer(buildApReport($dataBreachRecord->fresh()), '3.3')->values)
        ->toBe(['Drukkerij Van Dijk (verwerker)', 'Zorgverzekeraar (ontvanger)']);
});

it('skips a receiver without a description', function (): void {
    $organisation = OrganisationTestHelper::create();

    $receiver = Receiver::factory()->recycle($organisation)->create(['description' => null]);
    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $processingRecord->receivers()->attach($receiver);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();
    $dataBreachRecord->avgResponsibleProcessingRecords()->attach($processingRecord);

    expect(apAnswer(buildApReport($dataBreachRecord->fresh()), '3.3')->source)
        ->toBe(AnswerSource::MISSING);
});

it('offers the pseudonymisation of the linked processing as context', function (): void {
    $organisation = OrganisationTestHelper::create();

    // A processing without security measures is forced to have no pseudonymisation
    // either, and the observer then clears the description, so all three go together.
    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create([
        'has_security' => true,
        'has_pseudonymization' => true,
        'pseudonymization' => 'Klantnummers zijn vervangen door een hash.',
    ]);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();
    $dataBreachRecord->avgResponsibleProcessingRecords()->attach($processingRecord);

    $report = buildApReport($dataBreachRecord->fresh());

    // 8.1 itself asks whether the data were unreadable, which the register does
    // not record; the pseudonymisation is offered alongside it, not as the answer.
    expect(apAnswer($report, '8.1')->source)->toBe(AnswerSource::MISSING)
        ->and(apAnswer($report, '8.1b')->values)->toBe(['Klantnummers zijn vervangen door een hash.'])
        ->and(apAnswer($report, '8.1b')->source)->toBe(AnswerSource::DERIVED);
});

it('skips a stakeholder and a processing record that describe nothing', function (): void {
    $organisation = OrganisationTestHelper::create();

    $stakeholder = Stakeholder::factory()->recycle($organisation)->create(['description' => null]);
    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create([
        'has_pseudonymization' => false,
    ]);
    $processingRecord->stakeholders()->attach($stakeholder);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();
    $dataBreachRecord->avgResponsibleProcessingRecords()->attach($processingRecord);

    $report = buildApReport($dataBreachRecord->fresh());

    expect(apAnswer($report, '7.2')->hints)->toBe([])
        ->and(apAnswer($report, '8.1b')->source)->toBe(AnswerSource::MISSING);
});

it('treats a field holding only whitespace as missing', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'summary' => '   ',
    ]);

    expect(apAnswer(buildApReport($dataBreachRecord), '5.3')->source)
        ->toBe(AnswerSource::MISSING);
});

it('points at the data subjects of the linked processing when the breach says nothing', function (): void {
    $organisation = OrganisationTestHelper::create();

    $stakeholder = Stakeholder::factory()->recycle($organisation)->create([
        'description' => 'Patiënten van de poliklinieken',
    ]);
    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $processingRecord->stakeholders()->attach($stakeholder);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'involved_people' => null,
    ]);
    $dataBreachRecord->avgResponsibleProcessingRecords()->attach($processingRecord);

    $answer = apAnswer(buildApReport($dataBreachRecord->fresh()), '7.2');

    expect($answer->source)->toBe(AnswerSource::MISSING)
        ->and($answer->hints)->toBe(['Patiënten van de poliklinieken']);
});

it('points at the BSN of the linked processing without filling in 6.1', function (): void {
    $organisation = OrganisationTestHelper::create();

    $stakeholder = Stakeholder::factory()->recycle($organisation)->create([
        'citizen_service_numbers' => true,
        'criminal_law' => false,
    ]);
    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $processingRecord->stakeholders()->attach($stakeholder);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'personal_data_categories' => null,
        'personal_data_categories_other' => null,
    ]);
    $dataBreachRecord->avgResponsibleProcessingRecords()->attach($processingRecord);

    $answer = apAnswer(buildApReport($dataBreachRecord->fresh()), '6.1');

    expect($answer->source)->toBe(AnswerSource::MISSING)
        ->and($answer->hints)->toBe(['Burgerservicenummer (BSN)']);
});
