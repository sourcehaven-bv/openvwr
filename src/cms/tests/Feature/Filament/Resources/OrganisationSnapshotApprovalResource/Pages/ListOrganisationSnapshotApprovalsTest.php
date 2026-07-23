<?php

declare(strict_types=1);

use App\Filament\Resources\OrganisationSnapshotApprovalResource\Pages\ListOrganisationSnapshotApprovalItems;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the list page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshotApprovals = SnapshotApproval::factory()
        ->recycle($organisation)
        ->count(5)
        ->create([
            'snapshot_id' => Snapshot::factory(state: [
                'state' => Approved::$name,
            ]),
        ]);

    $snapshots = collect($snapshotApprovals)
        ->map(static function (SnapshotApproval $snapshotApproval): Snapshot {
            return $snapshotApproval->snapshot;
        });

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListOrganisationSnapshotApprovalItems::class)
        ->assertCanSeeTableRecords($snapshots);
});

it('loads correct list of items in all tabs', function (string $activeTab, int $assertCount): void {
    $organisation = OrganisationTestHelper::create();
    Snapshot::factory()
        ->recycle($organisation)
        ->count(5)
        ->state(new Sequence(
            ['state' => Established::$name],
            ['state' => Established::$name], // only reason to make a second established is to have diffent resultcounts
            ['state' => Obsolete::$name],
            ['state' => InReview::$name],
            ['state' => Approved::$name],
        ))
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListOrganisationSnapshotApprovalItems::class, ['activeTab' => $activeTab])
        ->assertCountTableRecords($assertCount);
})->with([
    [ListOrganisationSnapshotApprovalItems::TAB_ID_ALL, 5],
    [ListOrganisationSnapshotApprovalItems::TAB_ID_ESTABLISHED_OBSOLETE, 3],
    [ListOrganisationSnapshotApprovalItems::TAB_ID_INREVIEW_APPROVED, 2],
]);

it('filters on whether a snapshot was ever established', function (): void {
    $organisation = OrganisationTestHelper::create();

    $established = Snapshot::factory()
        ->recycle($organisation)
        ->count(2)
        ->create([
            'state' => Established::$name,
            'established_at' => now(),
        ]);

    $neverEstablished = Snapshot::factory()
        ->recycle($organisation)
        ->count(3)
        ->create([
            'state' => Obsolete::$name,
            'established_at' => null,
        ]);

    $page = $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListOrganisationSnapshotApprovalItems::class, [
            'activeTab' => ListOrganisationSnapshotApprovalItems::TAB_ID_ALL,
        ]);

    $page->filterTable('established_at', true)
        ->assertCanSeeTableRecords($established)
        ->assertCanNotSeeTableRecords($neverEstablished);

    $page->filterTable('established_at', false)
        ->assertCanSeeTableRecords($neverEstablished)
        ->assertCanNotSeeTableRecords($established);
});
