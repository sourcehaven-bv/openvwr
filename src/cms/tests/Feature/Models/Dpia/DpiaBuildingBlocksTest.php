<?php

/**
 * The small pieces the DPIA screens lean on: how a level is coloured, which
 * gegevens a collection singles out, and how a risk names itself when the
 * invuller has not given it a title yet.
 */

declare(strict_types=1);

use App\Enums\Dpia\PersonalDataType;
use App\Enums\Dpia\PrescanOutcome;
use App\Enums\Dpia\RiskLevel;
use App\Enums\StateColor;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaPersonalData;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;
use Tests\Helpers\Model\OrganisationTestHelper;

it('colours a risk level by severity', function (RiskLevel $level, StateColor $expected): void {
    expect($level->color())->toBe($expected);
})->with([
    'laag' => [RiskLevel::LOW, StateColor::SUCCESS],
    'gemiddeld' => [RiskLevel::MEDIUM, StateColor::WARNING],
    'hoog' => [RiskLevel::HIGH, StateColor::DANGER],
]);

// A mandatory DPIA is the alarming outcome, so it is the one shown in red.
it('colours a pre-scan outcome by how binding it is', function (
    PrescanOutcome $outcome,
    StateColor $expected,
): void {
    expect($outcome->color())->toBe($expected);
})->with([
    'verplicht' => [PrescanOutcome::REQUIRED, StateColor::DANGER],
    'aanbevolen' => [PrescanOutcome::RECOMMENDED, StateColor::WARNING],
    'niet verplicht' => [PrescanOutcome::NOT_REQUIRED, StateColor::SUCCESS],
]);

// The types that need a ground in paragraaf 12 are the ones flagged in red.
it('colours a persoonsgegeven type by how sensitive it is', function (
    PersonalDataType $type,
    StateColor $expected,
): void {
    expect($type->color())->toBe($expected);
})->with([
    'gewoon' => [PersonalDataType::ORDINARY, StateColor::GRAY],
    'gevoelig' => [PersonalDataType::SENSITIVE, StateColor::WARNING],
    'bijzonder' => [PersonalDataType::SPECIAL, StateColor::DANGER],
    'strafrechtelijk' => [PersonalDataType::CRIMINAL, StateColor::DANGER],
    'identificatienummer' => [PersonalDataType::NATIONAL_IDENTIFIER, StateColor::DANGER],
]);

it('picks out the high risks from a collection', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();

    $high = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Hoog risico',
        'level' => RiskLevel::HIGH,
    ]);
    DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Laag risico',
        'level' => RiskLevel::LOW,
    ]);

    expect($dpiaRecord->risks->highRisks()->pluck('id')->map->toString()->all())
        ->toBe([$high->id->toString()]);
});

it('picks out the gegevens that need a ground, and those still missing one', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();

    $withoutGround = DpiaPersonalData::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Gezondheidsgegevens',
        'type' => PersonalDataType::SPECIAL,
        'exception_ground' => null,
    ]);
    $withGround = DpiaPersonalData::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Burgerservicenummer',
        'type' => PersonalDataType::NATIONAL_IDENTIFIER,
        'exception_ground' => 'Wet algemene bepalingen burgerservicenummer',
    ]);
    DpiaPersonalData::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Naam',
        'type' => PersonalDataType::ORDINARY,
        'exception_ground' => null,
    ]);

    $personalData = $dpiaRecord->personalData;

    expect($personalData->requiringExceptionGround()->pluck('id')->map->toString()->all())
        ->toEqualCanonicalizing([$withoutGround->id->toString(), $withGround->id->toString()])
        ->and($personalData->missingExceptionGround()->pluck('id')->map->toString()->all())
        ->toBe([$withoutGround->id->toString()]);
});

it('names a risk by its title', function (): void {
    $organisation = OrganisationTestHelper::create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create(['title' => '  Onbevoegde inzage  ']);

    expect($risk->label())->toBe('Onbevoegde inzage');
});

// Without a title the description stands in, shortened so it still fits a
// list, because the invuller may not have named the risk yet.
it('falls back to the description when a risk has no title', function (): void {
    $organisation = OrganisationTestHelper::create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'title' => null,
        'description' => 'Beelden worden bekeken zonder melding',
    ]);

    expect($risk->label())->toBe('Beelden worden bekeken zonder melding');
});

it('shortens a long description used as the name', function (): void {
    $organisation = OrganisationTestHelper::create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'title' => null,
        'description' => str_repeat('a', 80),
    ]);

    expect($risk->label())->toBe(str_repeat('a', 57) . '...')
        ->and(mb_strlen($risk->label()))->toBe(60);
});

it('names an unnamed risk with a placeholder', function (): void {
    $organisation = OrganisationTestHelper::create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'title' => '  ',
        'description' => null,
    ]);

    expect($risk->label())->toBe(__('dpia_quality.unnamed'));
});

it('relates risks, maatregelen and persoonsgegevens back to their DPIA', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();

    $risk = DpiaRisk::factory()->recycle($organisation)->create(['dpia_record_id' => $dpiaRecord->id]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create(['dpia_record_id' => $dpiaRecord->id]);
    $personalData = DpiaPersonalData::factory()->recycle($organisation)
        ->create(['dpia_record_id' => $dpiaRecord->id]);

    expect($risk->dpiaRecord->id->toString())->toBe($dpiaRecord->id->toString())
        ->and($measure->dpiaRecord->id->toString())->toBe($dpiaRecord->id->toString())
        ->and($personalData->dpiaRecord->id->toString())->toBe($dpiaRecord->id->toString());
});

// Artikel 36: only a remaining high residual risk forces consultation, so the
// highest level has to survive a mix of measures.
it('reports the highest residual risk left after the maatregelen', function (
    array $levels,
    ?RiskLevel $expected,
    bool $requiresAp,
): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();

    foreach ($levels as $level) {
        DpiaMeasure::factory()->recycle($organisation)->create([
            'dpia_record_id' => $dpiaRecord->id,
            'residual_level' => $level,
        ]);
    }

    $dpiaRecord->refresh();

    expect($dpiaRecord->highestResidualRiskLevel())->toBe($expected)
        ->and($dpiaRecord->requiresApConsultation())->toBe($requiresAp);
})->with([
    'no maatregelen' => [[], null, false],
    'nothing scored' => [[null], null, false],
    'only laag' => [[RiskLevel::LOW, RiskLevel::LOW], RiskLevel::LOW, false],
    'laag and gemiddeld' => [[RiskLevel::LOW, RiskLevel::MEDIUM], RiskLevel::MEDIUM, false],
    'gemiddeld then laag' => [[RiskLevel::MEDIUM, RiskLevel::LOW], RiskLevel::MEDIUM, false],
    'one hoog' => [[RiskLevel::LOW, RiskLevel::HIGH], RiskLevel::HIGH, true],
    'unscored next to laag' => [[null, RiskLevel::LOW], RiskLevel::LOW, false],
]);
