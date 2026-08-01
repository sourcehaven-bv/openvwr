<?php

/**
 * The helpers the three repeaters lean on. They read the state of a single
 * repeater item, so they are exercised with that state directly instead of
 * through a rendered form.
 */

declare(strict_types=1);

use App\Enums\Dpia\PersonalDataType;
use App\Enums\Dpia\RiskLevel;
use App\Filament\Forms\Components\Repeater\DpiaMeasuresRepeater;
use App\Filament\Forms\Components\Repeater\DpiaPersonalDataRepeater;
use App\Filament\Resources\DpiaRecordResource\Pages\EditDpiaRecord;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaPersonalData;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;
use Tests\Helpers\Dpia\ArrayGet;
use Tests\Helpers\Model\OrganisationTestHelper;

it('asks for a ground only for the types that need one', function (mixed $type, bool $expected): void {
    expect(DpiaPersonalDataRepeater::requiresExceptionGround(new ArrayGet(['type' => $type])))
        ->toBe($expected);
})->with([
    'gewoon' => [PersonalDataType::ORDINARY->value, false],
    'gevoelig' => [PersonalDataType::SENSITIVE->value, false],
    'bijzonder' => [PersonalDataType::SPECIAL->value, true],
    'strafrechtelijk' => [PersonalDataType::CRIMINAL->value, true],
    'identificatienummer' => [PersonalDataType::NATIONAL_IDENTIFIER->value, true],
    'already an enum' => [PersonalDataType::SPECIAL, true],
    'nothing chosen' => [null, false],
    'empty string' => ['', false],
    'unknown value' => ['bestaat-niet', false],
]);

it('shows the notice only when a ground is needed', function (): void {
    $needed = DpiaPersonalDataRepeater::exceptionNotice(
        new ArrayGet(['type' => PersonalDataType::SPECIAL->value]),
    );
    $notNeeded = DpiaPersonalDataRepeater::exceptionNotice(
        new ArrayGet(['type' => PersonalDataType::ORDINARY->value]),
    );

    expect($needed?->toHtml())->toContain(__('dpia_record.personal_data_exception_notice'))
        ->and($notNeeded)->toBeNull();
});

// The checkboxes on a maatregel come from the risks entered in paragraaf 16,
// keyed by the repeater state key so an unsaved risk can be referred to too.
it('offers the risks of paragraaf 16 as options', function (): void {
    $options = DpiaMeasuresRepeater::riskOptionsFor([
        'record-1' => ['title' => 'Onbevoegde inzage'],
        'record-2' => ['title' => 'Datalek'],
    ]);

    expect($options)->toBe([
        'record-1' => 'Onbevoegde inzage',
        'record-2' => 'Datalek',
    ]);
});

it('leaves out risks that cannot be named yet', function (): void {
    $options = DpiaMeasuresRepeater::riskOptionsFor([
        'record-1' => ['title' => 'Wel een titel'],
        'record-2' => ['title' => ''],
        'record-3' => ['title' => null],
        'record-4' => 'geen array',
        5 => ['title' => 'Numerieke sleutel'],
    ]);

    expect($options)->toBe(['record-1' => 'Wel een titel']);
});

// The matrix hint and the item headers are closures on the repeater, so they
// only run once a form is actually rendered with a risk in it.
it('shows the level the matrix suggests on the form', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'title' => 'Onbevoegde inzage in camerabeelden',
        'likelihood' => RiskLevel::HIGH,
        'impact' => RiskLevel::HIGH,
        'level' => RiskLevel::HIGH,
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()])
        // The item header falls back on the title that was filled in.
        ->assertSee('Onbevoegde inzage in camerabeelden')
        ->assertSee(__('dpia_record.risk_matrix_suggestion', ['level' => RiskLevel::HIGH->label()]));
});

// The matrix is illustrative: a deviation is flagged, never corrected.
it('flags a chosen level that deviates from the matrix', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'likelihood' => RiskLevel::HIGH,
        'impact' => RiskLevel::HIGH,
        'level' => RiskLevel::LOW,
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()])
        ->assertSee(__('dpia_record.risk_matrix_deviation', ['level' => RiskLevel::HIGH->label()]));
});

// Filling in kans and impact proposes a level, but only while the invuller has
// not chosen one, so a deliberate choice is never overwritten.
it('fills in the suggested level when none was chosen yet', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'likelihood' => null,
        'impact' => null,
        'level' => null,
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $key = 'data.risks.record-' . $risk->id->toString();

    $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()])
        ->set($key . '.likelihood', RiskLevel::HIGH->value)
        ->set($key . '.impact', RiskLevel::HIGH->value)
        ->assertSet($key . '.level', RiskLevel::HIGH->value);
});

it('leaves a level that was already chosen alone', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    $risk = DpiaRisk::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'likelihood' => null,
        'impact' => null,
        'level' => RiskLevel::LOW,
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $key = 'data.risks.record-' . $risk->id->toString();

    $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()])
        ->set($key . '.likelihood', RiskLevel::HIGH->value)
        ->set($key . '.impact', RiskLevel::HIGH->value)
        ->assertSet($key . '.level', RiskLevel::LOW->value);
});

// The persoonsgegevens header falls back to a generic label until the invuller
// has described the gegeven.
it('labels a persoonsgegeven by its description', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    DpiaPersonalData::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Kenteken van het voertuig',
    ]);
    DpiaPersonalData::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => '',
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()])
        ->assertSee('Kenteken van het voertuig')
        ->assertSee(__('dpia_record.personal_data_item_label'));
});

// Artikel 36: a maatregel that leaves a high residual risk opens the AP fields.
it('shows the artikel 36 fields when a maatregel leaves a high residual risk', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Onvoldoende maatregel',
        'residual_level' => RiskLevel::HIGH,
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()])
        ->assertSee(__('dpia_record.measure_ap_advice'))
        ->assertSee(__('dpia_record.measure_monitoring_country'));
});

it('leaves the artikel 36 fields out when no high residual risk remains', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();
    DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Afdoende maatregel',
        'residual_level' => RiskLevel::LOW,
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()])
        ->assertDontSee(__('dpia_record.measure_ap_advice'));
});
