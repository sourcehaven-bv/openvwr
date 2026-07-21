<?php

declare(strict_types=1);

use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Filament\Resources\PersonalSnapshotApprovalResource;
use App\Filament\Resources\PersonalSnapshotApprovalResource\Pages\ListPersonalSnapshotApprovalItems;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\States\Snapshot\Approved;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the list page', function (): void {
    $this->asFilamentUser()
        ->get(PersonalSnapshotApprovalResource::getUrl())
        ->assertSuccessful();
});

it('loads the list items', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $snapshotApprovals = SnapshotApproval::factory()
        ->recycle($organisation)
        ->count(5)
        ->create([
            'assigned_to' => $user,
            'snapshot_id' => Snapshot::factory()->state([
                'state' => Approved::$name,
            ]),
        ]);

    $snapshots = $snapshotApprovals->map(static function (SnapshotApproval $snapshotApproval): Snapshot {
        return $snapshotApproval->snapshot;
    });

    $this->asFilamentUser($user)
        ->createLivewireTestable(ListPersonalSnapshotApprovalItems::class)
        ->set('activeTab', ListPersonalSnapshotApprovalItems::TAB_ID_ALL)
        ->assertCanSeeTableRecords($snapshots);
});

it('loads correct list of items in all tabs', function (string $activeTab, int $assertCount): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    SnapshotApproval::factory()
        ->recycle($organisation)
        ->count(3)
        ->state(new Sequence(
            ['status' => SnapshotApprovalStatus::APPROVED],
            ['status' => SnapshotApprovalStatus::DECLINED],
            ['status' => SnapshotApprovalStatus::UNKNOWN],
        ))
        ->create([
            'assigned_to' => $user,
            'snapshot_id' => Snapshot::factory()->state([
                'state' => Approved::$name,
            ]),
        ]);

    $this->asFilamentUser($user)
        ->createLivewireTestable(ListPersonalSnapshotApprovalItems::class, ['activeTab' => $activeTab])
        ->assertCountTableRecords($assertCount);
})->with([
    [ListPersonalSnapshotApprovalItems::TAB_ID_ALL, 3],
    [ListPersonalSnapshotApprovalItems::TAB_ID_REVIEWED, 2],
    [ListPersonalSnapshotApprovalItems::TAB_ID_UNREVIEWED, 1],
]);

it('can bulk approve', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $snapshotApprovalApproved = SnapshotApproval::factory()
        ->recycle($organisation)
        ->create([
            'assigned_to' => $user,
            'snapshot_id' => Snapshot::factory()->state([
                'state' => Approved::$name,
            ]),
            'status' => SnapshotApprovalStatus::APPROVED,
        ]);
    $snapshotApprovalDeclined = SnapshotApproval::factory()
        ->recycle($organisation)
        ->create([
            'assigned_to' => $user,
            'snapshot_id' => Snapshot::factory()->state([
                'state' => Approved::$name,
            ]),
            'status' => SnapshotApprovalStatus::DECLINED,
        ]);
    $snapshotApprovalUnknown = SnapshotApproval::factory()
        ->recycle($organisation)
        ->create([
            'assigned_to' => $user,
            'snapshot_id' => Snapshot::factory()->state([
                'state' => Approved::$name,
            ]),
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);

    $this->asFilamentUser($user)
        ->createLivewireTestable(ListPersonalSnapshotApprovalItems::class)
        ->set('activeTab', ListPersonalSnapshotApprovalItems::TAB_ID_ALL)
        ->assertCountTableRecords(3)
        ->assertCanSeeTableRecords([
            $snapshotApprovalApproved->snapshot,
            $snapshotApprovalDeclined->snapshot,
            $snapshotApprovalUnknown->snapshot,
        ])
        ->callTableBulkAction('snapshot_approval_approve', [
            $snapshotApprovalUnknown->snapshot,
        ])
        ->assertHasNoTableBulkActionErrors()
        ->assertSuccessful();

    $this->assertDatabaseHas(SnapshotApproval::class, [
        'id' => $snapshotApprovalUnknown->id,
        'status' => SnapshotApprovalStatus::APPROVED, // updated
    ]);
});
