<?php

/**
 * The register overviews, with rows in them. The columns that matter are
 * computed rather than stored - the highest residual risk and the pre-scan
 * outcome are derived on read - so they only run when a row is rendered.
 */

declare(strict_types=1);

use App\Enums\Dpia\DpiaSubjectType;
use App\Enums\Dpia\RiskLevel;
use App\Filament\Resources\DpiaPrescanRecordResource\Pages\ListDpiaPrescanRecords;
use App\Filament\Resources\DpiaRecordResource\Pages\ListDpiaRecords;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;
use Tests\Helpers\Model\OrganisationTestHelper;

it('shows the highest residual risk in the DPIA register', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create([
        'name' => 'Cameratoezicht parkeergarage',
        'subject_type' => DpiaSubjectType::PROCESSING,
    ]);
    DpiaRisk::factory()->recycle($organisation)->create(['dpia_record_id' => $dpiaRecord->id]);
    DpiaMeasure::factory()->recycle($organisation)->create([
        'dpia_record_id' => $dpiaRecord->id,
        'residual_level' => RiskLevel::MEDIUM,
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(ListDpiaRecords::class)
        ->assertCanSeeTableRecords([$dpiaRecord])
        ->assertSee('Cameratoezicht parkeergarage')
        ->assertSee(RiskLevel::MEDIUM->label())
        ->assertSee(DpiaSubjectType::PROCESSING->label());
});

// Without scored maatregelen there is no residual risk to show, which is a
// different statement from "laag".
it('says so when a DPIA has no scored risks yet', function (): void {
    $organisation = OrganisationTestHelper::create();
    $dpiaRecord = DpiaRecord::factory()->recycle($organisation)->create();

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(ListDpiaRecords::class)
        ->assertCanSeeTableRecords([$dpiaRecord])
        ->assertSee(__('dpia_record.no_risks'));
});

it('filters the DPIA register on a review that is due', function (): void {
    $organisation = OrganisationTestHelper::create();

    $due = DpiaRecord::factory()->recycle($organisation)->create([
        'name' => 'Toe aan herziening',
        'review_at' => now()->subDay()->format('Y-m-d'),
    ]);
    $notDue = DpiaRecord::factory()->recycle($organisation)->create([
        'name' => 'Nog niet toe aan herziening',
        'review_at' => now()->addYear()->format('Y-m-d'),
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(ListDpiaRecords::class)
        ->filterTable('review_due')
        ->assertCanSeeTableRecords([$due])
        ->assertCanNotSeeTableRecords([$notDue]);
});

// The outcome is recomputed on read, so the column stays correct even when a
// pre-scan was saved before a rule changed.
it('shows the recomputed outcome in the pre-scan register', function (): void {
    $organisation = OrganisationTestHelper::create();
    $prescanRecord = DpiaPrescanRecord::factory()->recycle($organisation)->create([
        'name' => 'Cameratoezicht parkeergarage',
        'ap_criteria' => ['cameratoezicht'],
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(ListDpiaPrescanRecords::class)
        ->assertCanSeeTableRecords([$prescanRecord])
        ->assertSee('Cameratoezicht parkeergarage')
        ->assertSee(__('dpia_prescan_record.outcome_verplicht'));
});

it('shows a pre-scan that concludes no DPIA is needed', function (): void {
    $organisation = OrganisationTestHelper::create();
    $prescanRecord = DpiaPrescanRecord::factory()->recycle($organisation)->create([
        'new_legislation' => false,
        'departmental_policy' => false,
        'public_cloud' => false,
        'ap_criteria' => [],
        'edpb_criteria' => [],
        'international_transfer' => false,
        'digital_service' => false,
        'minors' => false,
        'algorithm' => false,
        'high_risk_ai' => false,
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(ListDpiaPrescanRecords::class)
        ->assertCanSeeTableRecords([$prescanRecord])
        ->assertSee(__('dpia_prescan_record.outcome_niet_verplicht'));
});
