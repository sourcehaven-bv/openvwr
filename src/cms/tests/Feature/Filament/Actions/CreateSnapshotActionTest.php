<?php

declare(strict_types=1);

namespace Filament\Actions;

use App\Enums\Authorization\Role;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Mail\SnapshotApproval\ApprovalRequest;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\Helpers\Model\OrganisationTestHelper;

use function __;
use function expect;
use function it;

it('can create a snapshot', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    expect($avgResponsibleProcessingRecord->snapshots)
        ->toHaveCount(0);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->callAction('snapshot_create')
        ->assertSuccessful()
        ->assertNotified(__('snapshot.created'));

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->snapshots)
        ->toHaveCount(1);
});

it('cannot create a snapshot if validation fails', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'avg_responsible_processing_record_service_id' => null,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->callAction('snapshot_create')
        ->assertNotNotified(__('snapshot.created'));

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->snapshots)
        ->toHaveCount(0);
});

it('will notify po', function (): void {
    Mail::fake();

    $organisation = OrganisationTestHelper::create();
    User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->callAction('snapshot_create')
        ->assertSuccessful();

    Mail::assertQueued(ApprovalRequest::class);
});

it('will not notify po if unchecked', function (): void {
    Mail::fake();

    $organisation = OrganisationTestHelper::create();
    User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->callAction('snapshot_create', [
            'notify_po' => false,
        ])
        ->assertSuccessful();

    Mail::assertNotQueued(ApprovalRequest::class);
});
