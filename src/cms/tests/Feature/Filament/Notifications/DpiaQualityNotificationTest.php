<?php

/**
 * The aandachtspunten are advisory: they are reported after saving instead of
 * blocking it, because a DPIA in progress is allowed to be incomplete. They
 * are therefore asserted through the edit page, which is where they surface.
 */

declare(strict_types=1);

use App\Enums\Dpia\RiskLevel;
use App\Filament\Resources\DpiaRecordResource\Pages\EditDpiaRecord;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;
use App\Services\Dpia\DpiaQualityChecker;
use Tests\Helpers\Model\OrganisationTestHelper;

it('does not warn when a DPIA has no aandachtspunten', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create(['outside_eea' => false]);

    expect(app(DpiaQualityChecker::class)->hasFindings($dpiaRecord))->toBeFalse();

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotNotified(__('dpia_quality.count', ['count' => 1]));
});

it('warns about the aandachtspunten after saving', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create([
        'outside_eea' => false,
        'residual_risk_acceptance' => null,
        'ap_consultation' => null,
    ]);

    // A high residual risk without an AP consultation and without an accepted
    // rest risk: two findings at once.
    DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'description' => 'Onvoldoende maatregel',
        'residual_level' => RiskLevel::HIGH,
    ]);

    $dpiaRecord->refresh();
    $count = count(app(DpiaQualityChecker::class)->check($dpiaRecord));

    expect($count)->toBeGreaterThan(0);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified(trans_choice('dpia_quality.count', $count, ['count' => $count]));
});

// Only the first few are listed and the rest are summarised, so a DPIA that is
// barely started does not produce an unreadable wall of text.
it('summarises the rest when there are more aandachtspunten than it lists', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create([
        'outside_eea' => true,
        'transfer_mechanism' => null,
        'residual_risk_acceptance' => null,
        'ap_consultation' => null,
    ]);

    // Six risks without a maatregel: well past the four that get listed.
    for ($i = 1; $i <= 6; $i++) {
        DpiaRisk::factory()->recycle($organisation)->create([
            'dpia_record_id' => $dpiaRecord->id,
            'title' => 'Risico ' . $i,
        ]);
    }

    $dpiaRecord->refresh();
    $count = count(app(DpiaQualityChecker::class)->check($dpiaRecord));

    expect($count)->toBeGreaterThan(4);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaRecord::class, ['record' => $dpiaRecord->id->toString()])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified(trans_choice('dpia_quality.count', $count, ['count' => $count]));
});
