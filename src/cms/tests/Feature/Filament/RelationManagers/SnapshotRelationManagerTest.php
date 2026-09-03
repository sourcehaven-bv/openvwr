<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Filament\Pages\ConceptEditRecord;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\ViewAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\Established;
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

// The row and the header button carry the same label, so they have to mean the same
// thing. On the edit page the row hands the work to that button instead of linking back
// to the page the user is already on, which looked like a submit and did nothing.
it('submits from the concept row instead of linking to the current page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create(['state' => Concept::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(SnapshotsRelationManager::class, [
            'ownerRecord' => $avgResponsibleProcessingRecord,
            'pageClass' => EditAvgResponsibleProcessingRecord::class,
        ])
        ->assertTableActionVisible('snapshot_submit_for_review', $snapshot)
        ->assertTableActionDoesNotHaveUrl('snapshot_submit_for_review', $snapshot)
        ->callTableAction('snapshot_submit_for_review', $snapshot)
        ->assertDispatchedTo(
            EditAvgResponsibleProcessingRecord::class,
            ConceptEditRecord::SUBMIT_FOR_REVIEW_EVENT,
        );
});

// There is no form to save on the view page, so the row keeps taking the user to the
// page that has one rather than pretending it can submit from here.
it('links to the edit page when the table is not on it', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create(['state' => Concept::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(SnapshotsRelationManager::class, [
            'ownerRecord' => $avgResponsibleProcessingRecord,
            'pageClass' => ViewAvgResponsibleProcessingRecord::class,
        ])
        ->assertTableActionHasUrl(
            'snapshot_submit_for_review',
            AvgResponsibleProcessingRecordResource::getUrl('edit', [
                'record' => $avgResponsibleProcessingRecord,
            ]),
            $snapshot,
        );
});

// Submitting needs the create permission, and the header button checks it. A row that
// offered the same thing to a user who may not do it would only produce a refusal.
it('hides the row action from a user who may not submit', function (): void {
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
        Permission::CORE_ENTITY_UPDATE,
        Permission::SNAPSHOT_VIEW,
    ]);
    $this->withFilamentSession($user, $organisation);

    $this->createLivewireTestable(SnapshotsRelationManager::class, [
        'ownerRecord' => $avgResponsibleProcessingRecord,
        'pageClass' => EditAvgResponsibleProcessingRecord::class,
    ])
        ->assertCanSeeTableRecords([$snapshot])
        ->assertTableActionHidden('snapshot_submit_for_review', $snapshot);
});

// Only a concept can be submitted; anything further along has left the form behind.
it('hides the row action on a version that is no longer a concept', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create(['state' => Established::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(SnapshotsRelationManager::class, [
            'ownerRecord' => $avgResponsibleProcessingRecord,
            'pageClass' => EditAvgResponsibleProcessingRecord::class,
        ])
        ->assertCanSeeTableRecords([$snapshot])
        ->assertTableActionHidden('snapshot_submit_for_review', $snapshot);
});
