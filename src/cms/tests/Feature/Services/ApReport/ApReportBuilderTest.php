<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Models\Address;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\Stakeholder;
use App\Models\User;
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

    // 3.2.1 asks who files the notification, which is decided at filing time
    // and is deliberately not a field anywhere in the register.
    expect(apAnswer($report, '3.2.1')->source)->toBe(AnswerSource::MISSING);
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

it('takes the AP-only notification fields from the data breach record', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'how_discovered' => 'Een medewerker meldde een verdachte e-mail.',
        'late_notification_reason' => 'De omvang was pas na forensisch onderzoek duidelijk.',
        'nature_of_breach' => ['Persoonsgegevens (mogelijk) ingezien door onbevoegden'],
        'risk_severity' => 'Aanzienlijk',
        'reported_to_involved_count' => 240,
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '4.3')->values)->toBe(['Een medewerker meldde een verdachte e-mail.'])
        ->and(apAnswer($report, '4.5')->values)
        ->toBe(['De omvang was pas na forensisch onderzoek duidelijk.'])
        ->and(apAnswer($report, '5.1')->values)
        ->toBe(['Persoonsgegevens (mogelijk) ingezien door onbevoegden'])
        ->and(apAnswer($report, '9.3')->values)->toBe(['Aanzienlijk'])
        ->and(apAnswer($report, '10.1.3')->values)->toBe(['240'])
        ->and(apAnswer($report, '10.1.3')->source)->toBe(AnswerSource::RECORDED);
});

it('replaces the bare "other" tick with the free text typed behind it', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'other_supervisors' => ['De Nederlandsche Bank (DNB)', 'Andere toezichthouder'],
        'other_supervisors_other' => 'De Kansspelautoriteit',
        'affected_groups' => ['Werknemers', 'Anders'],
        'affected_groups_other' => 'Oud-medewerkers',
        'consequences_controller' => ['Anders'],
        'consequences_controller_other' => 'Verstoring van de dienstverlening',
        'consequences_data_subjects' => ['Reputatieschade'],
        'consequences_data_subjects_other' => null,
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '1.3')->values)
        ->toBe(['De Nederlandsche Bank (DNB)', 'Andere toezichthouder, namelijk: De Kansspelautoriteit'])
        ->and(apAnswer($report, '7.1')->values)
        ->toBe(['Werknemers', 'Anders, namelijk: Oud-medewerkers'])
        ->and(apAnswer($report, '9.1')->values)
        ->toBe(['Anders, namelijk: Verstoring van de dienstverlening'])
        ->and(apAnswer($report, '9.2')->values)->toBe(['Reputatieschade']);
});

it('adds the explanation the AP asks for next to the answer', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'record_count' => '1200',
        'record_count_explanation' => 'Schatting op basis van de exportlogs.',
        'protection_beforehand' => ['Versleuteld (encryptie)'],
        'protection_beforehand_explanation' => 'AES-256 op de hele schijf.',
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '6.3.1')->values)->toBe(['1200', 'Schatting op basis van de exportlogs.'])
        ->and(apAnswer($report, '8.1')->values)->toBe(['Versleuteld (encryptie)', 'AES-256 op de hele schijf.']);
});

it('leaves an explanation-only question missing when nothing was filled in', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'record_count' => null,
        'record_count_explanation' => null,
        'protection_beforehand' => null,
        'protection_beforehand_explanation' => null,
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '6.3.1')->source)->toBe(AnswerSource::MISSING)
        ->and(apAnswer($report, '8.1')->source)->toBe(AnswerSource::MISSING);
});

it('gives the exact number of data subjects when it is known', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'affected_count_known' => true,
        'affected_count' => 42,
        'affected_count_min' => 10,
        'affected_count_max' => 100,
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '7.3')->values)->toBe(['42']);
});

it('gives a range of data subjects when the exact number is not known', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'affected_count_known' => false,
        'affected_count_min' => 10,
        'affected_count_max' => 100,
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '7.3')->values)->toBe(['10 - 100']);
});

it('marks an open end of the range so the gap is visible', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'affected_count_known' => false,
        'affected_count_min' => 10,
        'affected_count_max' => null,
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '7.3')->values)->toBe(['10 - ?']);
});

it('reports the number of data subjects as missing when neither end is known', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'affected_count_known' => false,
        'affected_count_min' => null,
        'affected_count_max' => null,
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '7.3')->source)->toBe(AnswerSource::MISSING);
});

it('answers the international questions from the data breach record', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'cross_border' => true,
        'cross_border_countries' => 'België en Duitsland',
        'reported_other_dpas' => 'Gemeld bij de Belgische Gegevensbeschermingsautoriteit.',
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '2.1.1')->values)->toBe([__('general.yes'), 'België en Duitsland'])
        ->and(apAnswer($report, '2.2.1')->values)
        ->toBe(['Gemeld bij de Belgische Gegevensbeschermingsautoriteit.']);
});

it('answers no to the cross-border question without listing countries', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create([
        'cross_border' => false,
        'cross_border_countries' => null,
    ]);

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '2.1.1')->values)->toBe([__('general.no')]);
});

it('takes the organisation details the AP asks for from the organisation', function (): void {
    $organisation = OrganisationTestHelper::create([
        'coc_number' => '12345678',
        'fg_registration_number' => 'FG012345',
        'sector' => 'Openbaar bestuur',
    ]);
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '3.1.1d')->values)->toBe(['FG012345'])
        ->and(apAnswer($report, '3.1.1e')->values)->toBe(['12345678'])
        ->and(apAnswer($report, '3.1.2')->values)->toBe(['Openbaar bestuur']);
});

it('suggests the data protection officials as contact person for the AP', function (): void {
    $organisation = OrganisationTestHelper::create();

    $user = User::factory()->create(['name' => 'Nadia de Wit', 'email' => 'fg@example.com']);
    $user->organisations()->attach($organisation);
    $user->organisationRoles()->create([
        'organisation_id' => $organisation->id,
        'role' => Role::DATA_PROTECTION_OFFICIAL,
    ]);

    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();

    $report = buildApReport($dataBreachRecord);
    $answer = apAnswer($report, '3.2.2');

    // Derived, not recorded: the officer may want to name someone else as the
    // point of contact for this particular breach.
    expect($answer->source)->toBe(AnswerSource::DERIVED)
        ->and($answer->values)->toBe(['Nadia de Wit (fg@example.com)']);
});

it('leaves the contact person open when the organisation has no data protection official', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dataBreachRecord = DataBreachRecord::factory()->recycle($organisation)->create();

    $report = buildApReport($dataBreachRecord);

    expect(apAnswer($report, '3.2.2')->source)->toBe(AnswerSource::MISSING);
});
