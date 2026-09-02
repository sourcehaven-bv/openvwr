<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Filament\Infolists\Components\SnapshotEstablishAction;
use App\Filament\Infolists\Tabs\Snapshot\ViewInfoTab;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

/**
 * The colour the establish button resolves to for this snapshot, which is how it signals
 * whether the version is ready to be established.
 */
function establishActionColor(Snapshot $snapshot): string
{
    $method = new ReflectionMethod(SnapshotEstablishAction::class, 'isReadyToEstablish');

    return $method->invoke(null, $snapshot) ? 'success' : 'warning';
}

// Vaststellen keeps its own button beside the status flow, because it first walks the
// user through the related entities and the approvals. The other transitions are a plain
// choice and share the "Status aanpassen" list.

it('establishes an approved version', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create(['state' => Approved::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, ['record' => $snapshot->id])
        ->callInfolistAction(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_establish');

    expect($snapshot->refresh()->state)
        ->toBeInstanceOf(Established::class);
});

// The point of the separate button: establishing may not be reachable through the plain
// radio list, because that would skip the two checks entirely.
it('does not offer established in the status change list', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create(['state' => Approved::class]);

    // Not merely absent from the options: a request carrying it anyway is refused too,
    // because the list is the page's offer rather than a promise about the request.
    $test = $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, ['record' => $snapshot->id]);

    expect(static function () use ($test): void {
        $test->callInfolistAction(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_status_change', data: [
            'state' => Established::$name,
        ]);
    })->toThrow(InvalidArgumentException::class);

    expect($snapshot->refresh()->state)
        ->toBeInstanceOf(Approved::class);
});

// A version under review may be established directly — the organisation decides whether
// it goes past a Privacy Officer first — so the button is offered there as well. The two
// steps are what tell the user whether that is a good idea yet.
it('is offered on a version that is still under review', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create(['state' => InReview::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, ['record' => $snapshot->id])
        ->assertInfolistActionVisible(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_establish');
});

it('is hidden on a version that is finished', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create(['state' => Obsolete::class]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, ['record' => $snapshot->id])
        ->assertInfolistActionHidden(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_establish');
});

// The button's colour is the readiness signal: green once the approvals are in and every
// related entity is established too, amber while something is still outstanding.
it('turns green once the approvals are in and related entities are established', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create(['state' => Approved::class]);
    SnapshotApproval::factory()->create([
        'snapshot_id' => $snapshot->id,
        'status' => SnapshotApprovalStatus::APPROVED,
    ]);

    expect(establishActionColor($snapshot))->toBe('success');
});

it('stays amber while a related entity is not established', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create(['state' => Approved::class]);
    SnapshotApproval::factory()->create([
        'snapshot_id' => $snapshot->id,
        'status' => SnapshotApprovalStatus::APPROVED,
    ]);

    // A related entity whose own versions never reached "Vastgesteld".
    $relatedRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    Snapshot::factory()
        ->recycle($organisation)
        ->for($relatedRecord, 'snapshotSource')
        ->create(['state' => InReview::class]);
    RelatedSnapshotSource::factory()->create([
        'snapshot_id' => $snapshot->id,
        'snapshot_source_id' => $relatedRecord->id,
        'snapshot_source_type' => $relatedRecord::class,
    ]);

    expect(establishActionColor($snapshot))->toBe('warning');
});

// The permission is checked in visible(), and that is enough to be a control rather
// than a courtesy: Filament's isDisabled() is `disabled || hidden`, and both
// mountInfolistAction and callMountedInfolistAction refuse a disabled action. A forged
// Livewire call that skips the button therefore never reaches the action closure.
// Pinned here because establishing is what fixes a version as the official one.
it('refuses a forged establish from a user without the permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create(['state' => Approved::class]);

    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::CORE_ENTITY_VIEW,
        Permission::SNAPSHOT_VIEW,
    ]);

    // Deliberately not callInfolistAction(): that helper asserts the button is visible
    // first, which is exactly the step an attacker skips. These are the raw calls.
    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(ViewSnapshot::class, ['record' => $snapshot->id])
        ->call('mountInfolistAction', 'snapshot_establish', ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'infolist')
        ->call('callMountedInfolistAction');

    expect($snapshot->refresh()->state)->toBeInstanceOf(Approved::class);
});
