<?php

declare(strict_types=1);

use App\Filament\Infolists\Tabs\Snapshot\ViewInfoTab;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use Tests\Helpers\Model\OrganisationTestHelper;

// The status button lives beside the status flow instead of in the page header, and
// offers the reachable states as one list rather than a dropdown of separate buttons.

it('changes the status to the chosen state', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create(['state' => InReview::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, ['record' => $snapshot->id])
        ->callInfolistAction(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_status_change', data: [
            'state' => Approved::$name,
        ]);

    expect($snapshot->refresh()->state)
        ->toBeInstanceOf(Approved::class);
});

it('is hidden on a snapshot that can no longer move', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create(['state' => Obsolete::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, ['record' => $snapshot->id])
        ->assertInfolistActionHidden(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_status_change');
});

it('no longer offers the status change in the page header', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create(['state' => Established::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, ['record' => $snapshot->id])
        ->assertActionDoesNotExist('snapshot_transition_to_obsolete');
});
