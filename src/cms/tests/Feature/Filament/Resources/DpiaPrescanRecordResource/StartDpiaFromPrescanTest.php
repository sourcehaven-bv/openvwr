<?php

/**
 * The handover from pre-scan to DPIA. What the pre-scan already established
 * should not have to be typed again, and the link back has to survive, because
 * that link is what records why the DPIA exists.
 */

declare(strict_types=1);

use App\Enums\Dpia\DpiaSubjectType;
use App\Enums\RegisterLayout;
use App\Filament\Resources\DpiaPrescanRecordResource;
use App\Filament\Resources\DpiaPrescanRecordResource\Pages\EditDpiaPrescanRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the pre-scan edit page in all layouts', function (RegisterLayout $registerLayout): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, ['register_layout' => $registerLayout]);
    $prescanRecord = DpiaPrescanRecord::factory()->recycle($organisation)->create();

    $this->asFilamentUser($user)
        ->get(DpiaPrescanRecordResource::getUrl('edit', ['record' => $prescanRecord], tenant: $organisation))
        ->assertOk();
})->with(RegisterLayout::cases());

it('starts a DPIA from a pre-scan and carries the answers over', function (): void {
    $organisation = OrganisationTestHelper::create();
    $prescanRecord = DpiaPrescanRecord::factory()->recycle($organisation)->create([
        'name' => 'Cameratoezicht parkeergarage',
        'description' => 'Voorgenomen cameratoezicht onder het hoofdkantoor.',
        'ap_criteria' => ['cameratoezicht'],
        'new_legislation' => false,
        'outside_eea' => false,
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaPrescanRecord::class, ['record' => $prescanRecord->id->toString()])
        ->callAction('start_dpia');

    $dpiaRecord = DpiaRecord::query()->where('organisation_id', $organisation->id)->sole();

    expect($dpiaRecord->name)->toBe('Cameratoezicht parkeergarage')
        ->and($dpiaRecord->proposal_description)->toBe('Voorgenomen cameratoezicht onder het hoofdkantoor.')
        ->and($dpiaRecord->dpia_prescan_record_id?->toString())->toBe($prescanRecord->id->toString())
        ->and($dpiaRecord->subject_type)->toBe(DpiaSubjectType::PROCESSING);
});

// A pre-scan about new legislation produces a DPIA op regelgeving, which is a
// different track in the Rijksmodel.
it('starts a DPIA op regelgeving when the pre-scan is about new legislation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $prescanRecord = DpiaPrescanRecord::factory()->recycle($organisation)->create([
        'new_legislation' => true,
        'outside_eea' => true,
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaPrescanRecord::class, ['record' => $prescanRecord->id->toString()])
        ->callAction('start_dpia');

    $dpiaRecord = DpiaRecord::query()->where('organisation_id', $organisation->id)->sole();

    expect($dpiaRecord->subject_type)->toBe(DpiaSubjectType::REGULATION)
        ->and($dpiaRecord->outside_eea)->toBeTrue();
});

it('carries the linked verwerkingen over to the DPIA', function (): void {
    $organisation = OrganisationTestHelper::create();
    // The button follows the evaluator, not the stored outcome, so the
    // criterion that makes a DPIA mandatory has to be set here.
    $prescanRecord = DpiaPrescanRecord::factory()->recycle($organisation)->create([
        'ap_criteria' => ['cameratoezicht'],
    ]);
    $processingRecord = AvgResponsibleProcessingRecord::factory()->recycle($organisation)->create();
    $prescanRecord->avgResponsibleProcessingRecords()->sync([$processingRecord->id->toString()]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaPrescanRecord::class, ['record' => $prescanRecord->id->toString()])
        ->callAction('start_dpia');

    $dpiaRecord = DpiaRecord::query()->where('organisation_id', $organisation->id)->sole();

    expect($dpiaRecord->avgResponsibleProcessingRecords->pluck('id')->map->toString()->all())
        ->toBe([$processingRecord->id->toString()]);
});

// The button routes around nothing: a pre-scan that concludes no DPIA is
// needed does not offer it.
it('does not offer to start a DPIA when the pre-scan says it is not required', function (): void {
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

    $this->createLivewireTestable(EditDpiaPrescanRecord::class, ['record' => $prescanRecord->id->toString()])
        ->assertActionHidden('start_dpia');
});

it('offers to start a DPIA when one AP criterion is ticked', function (): void {
    $organisation = OrganisationTestHelper::create();
    $prescanRecord = DpiaPrescanRecord::factory()->recycle($organisation)->create([
        'ap_criteria' => ['cameratoezicht'],
    ]);

    $this->asFilamentOrganisationUser($organisation);

    $this->createLivewireTestable(EditDpiaPrescanRecord::class, ['record' => $prescanRecord->id->toString()])
        ->assertActionVisible('start_dpia');
});
