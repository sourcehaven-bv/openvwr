<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the table', function (): void {
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create();
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create();

    $this->asFilamentUser()
        ->createLivewireTestable(SnapshotsRelationManager::class, [
            'ownerRecord' => $avgResponsibleProcessingRecord,
            'pageClass' => EditAvgResponsibleProcessingRecord::class,
        ])
        ->assertCanSeeTableRecords([$snapshot]);
});

it('reloads the snapshots-table', function (): void {
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create();

    $snapshotRelationManager = $this->asFilamentUser()
        ->createLivewireTestable(SnapshotsRelationManager::class, [
            'ownerRecord' => $avgResponsibleProcessingRecord,
            'pageClass' => EditAvgResponsibleProcessingRecord::class,
        ])
        ->assertCanSeeTableRecords([]);

    $snapshot = Snapshot::factory()->make();
    $avgResponsibleProcessingRecord->snapshots()->save($snapshot);

    $snapshotRelationManager->fireEvent(SnapshotsRelationManager::REFRESH_TABLE_EVENT)
        ->assertCanSeeTableRecords([$snapshot]);
});

// The concept row links to the owner's edit page, so a user who may look but not edit
// gets no link at all rather than one to a page they cannot open.
it('offers no submit link to a user who may not edit the record', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create(['state' => Concept::class]);

    $user = UserTestHelper::createForOrganisation($organisation);
    $this->withPermissions($user, [
        Permission::CORE_ENTITY_VIEW,
        Permission::SNAPSHOT_VIEW,
        Permission::SNAPSHOT_CREATE,
    ]);
    $this->withFilamentSession($user, $organisation);

    $this->createLivewireTestable(SnapshotsRelationManager::class, [
        'ownerRecord' => $avgResponsibleProcessingRecord,
        'pageClass' => EditAvgResponsibleProcessingRecord::class,
    ])
        ->assertCanSeeTableRecords([$snapshot])
        ->assertTableActionDoesNotHaveUrl('snapshot_submit_for_review', $snapshot);
});
