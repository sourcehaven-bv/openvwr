<?php

declare(strict_types=1);

use App\Enums\Dpia\PersonalDataType;
use App\Enums\Dpia\RiskLevel;
use App\Filament\Forms\Components\Repeater\DpiaMeasuresRepeater;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaPersonalData;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;
use App\Services\Dpia\DpiaQualityChecker;
use Tests\Helpers\Model\OrganisationTestHelper;

/**
 * @return array<int, string>
 */
function findingKeys(DpiaRecord $dpiaRecord): array
{
    $keys = [];

    foreach (app(DpiaQualityChecker::class)->check($dpiaRecord) as $finding) {
        $keys[] = $finding->key;
    }

    return $keys;
}

// An empty DPIA is a DPIA in progress, not a broken one.
it('reports nothing for an empty DPIA', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create(['outside_eea' => false]);

    expect(findingKeys($dpiaRecord))->toBe([]);
});

it('flags bijzondere persoonsgegevens without an exception ground', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create(['outside_eea' => false]);

    DpiaPersonalData::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'type' => PersonalDataType::SPECIAL,
        'exception_ground' => null,
    ]);

    expect(findingKeys($dpiaRecord))->toContain('personal_data_without_exception_ground');
});

it('accepts bijzondere persoonsgegevens with a ground', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create(['outside_eea' => false]);

    DpiaPersonalData::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'type' => PersonalDataType::SPECIAL,
        'exception_ground' => 'Artikel 9 lid 2 onder g AVG, uitgewerkt in artikel 30 UAVG.',
    ]);

    expect(findingKeys($dpiaRecord))->not->toContain('personal_data_without_exception_ground');
});

// "Gevoelig" is not a legal category, so it needs no ground.
it('does not ask a ground for gewone or gevoelige gegevens', function (PersonalDataType $type): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create(['outside_eea' => false]);

    DpiaPersonalData::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'type' => $type,
        'exception_ground' => null,
    ]);

    expect(findingKeys($dpiaRecord))->toBe([]);
})->with([
    'gewoon' => PersonalDataType::ORDINARY,
    'gevoelig' => PersonalDataType::SENSITIVE,
]);

it('flags a transfer outside the EEA without a mechanism', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create([
        'outside_eea' => true,
        'transfer_mechanism' => null,
    ]);

    expect(findingKeys($dpiaRecord))->toContain('transfer_without_mechanism');
});

it('flags a risk that no measure addresses', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create(['outside_eea' => false]);

    DpiaRisk::factory()->recycle($organisation)->create(['dpia_record_id' => $dpiaRecord->id]);

    expect(findingKeys($dpiaRecord))->toContain('risk_without_measure');
});

it('flags a matrix deviation without motivation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create(['outside_eea' => false]);

    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'likelihood' => RiskLevel::HIGH,
        'impact' => RiskLevel::HIGH,
        'level' => RiskLevel::LOW,
        'level_motivation' => null,
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'residual_level' => RiskLevel::LOW,
    ]);
    $measure->risks()->sync([$risk->id->toString()]);

    expect(findingKeys($dpiaRecord->fresh()))->toContain('risk_deviates_without_motivation');
});

// Deviating from the matrix is allowed as long as the reasoning is there.
it('accepts a motivated matrix deviation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create(['outside_eea' => false]);

    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'likelihood' => RiskLevel::HIGH,
        'impact' => RiskLevel::HIGH,
        'level' => RiskLevel::LOW,
        'level_motivation' => 'De kans is door technische maatregelen feitelijk verwaarloosbaar.',
    ]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'residual_level' => RiskLevel::LOW,
    ]);
    $measure->risks()->sync([$risk->id->toString()]);

    expect(findingKeys($dpiaRecord->fresh()))->not->toContain('risk_deviates_without_motivation');
});

it('flags a high residual risk without AP consultation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create([
        'outside_eea' => false,
        'ap_consultation_required' => false,
        'residual_risk_acceptance' => 'Aanvaard door de directeur.',
    ]);

    $risk = DpiaRisk::factory()->recycle($organisation)->create(['dpia_record_id' => $dpiaRecord->id]);
    $measure = DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'residual_level' => RiskLevel::HIGH,
    ]);
    $measure->risks()->sync([$risk->id->toString()]);

    expect(findingKeys($dpiaRecord->fresh()))->toContain('high_residual_risk_without_ap');
});

it('flags a measure that addresses no risk', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create(['outside_eea' => false]);

    DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'residual_level' => RiskLevel::LOW,
    ]);

    expect(findingKeys($dpiaRecord))->toContain('measure_without_risk');
});

it('produces a readable Dutch message', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create(['outside_eea' => false]);

    DpiaPersonalData::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Camerabeelden',
        'type' => PersonalDataType::SPECIAL,
        'exception_ground' => null,
    ]);

    $findings = app(DpiaQualityChecker::class)->check($dpiaRecord);

    expect($findings[0]->message())->toContain('Camerabeelden')
        ->and($findings[0]->paragraphLabel())->toBe('Paragraaf 12');
});

// Paragraaf 17 offers the risks of paragraaf 16 as checkboxes. A risk typed in
// the same session has no id yet, so the options come from the form state --
// otherwise the invuller would have to save halfway through and retype.
it('offers unsaved risks as measure options', function (): void {
    $options = DpiaMeasuresRepeater::riskOptionsFor([
        'newrisk' => ['title' => 'Onterechte identificatie'],
        'record-abc' => ['title' => 'Beelden te lang bewaard'],
        'empty' => ['title' => ''],
    ]);

    expect($options)->toBe([
        'newrisk' => 'Onterechte identificatie',
        'record-abc' => 'Beelden te lang bewaard',
    ]);
});

// Paragraaf 8: a transfer outside the EEA needs a mechanism, but only when the
// DPIA actually says there is one.
it('does not ask for a transfer mechanism when nothing leaves the EEA', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create([
        'outside_eea' => false,
        'transfer_mechanism' => null,
    ]);

    expect(findingKeys($dpiaRecord))->not->toContain('transfer_without_mechanism');
});

it('accepts a transfer outside the EEA that names its mechanism', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create([
        'outside_eea' => true,
        'transfer_mechanism' => 'Standaardbepalingen inzake gegevensbescherming',
    ]);

    expect(findingKeys($dpiaRecord))->not->toContain('transfer_without_mechanism');
});

// An unnamed risk still has to be reportable, so it falls back to a
// placeholder rather than an empty bullet.
it('names an undescribed maatregel with a placeholder in the findings', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create(['outside_eea' => false]);
    DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => '   ',
    ]);

    $dpiaRecord->refresh();

    $messages = [];

    foreach (app(DpiaQualityChecker::class)->check($dpiaRecord) as $finding) {
        $messages[] = $finding->message();
    }

    expect(implode(' ', $messages))->toContain(__('dpia_quality.unnamed'));
});
