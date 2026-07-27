<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Filament\Resources\SnapshotResource;
use App\Filament\Resources\SnapshotResource\Pages\CompareSnapshots;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\RelatedSnapshotSource;
use App\Models\Snapshot;
use App\Models\SnapshotData;
use App\Models\System;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

/**
 * @return array{0: Snapshot, 1: Snapshot}
 */
function createComparableSnapshots(
    AvgResponsibleProcessingRecord $source,
    string $fromMarkdown,
    string $toMarkdown,
): array {
    $organisation = $source->getOrganisation();

    $from = Snapshot::factory()
        ->recycle($organisation)
        ->for($source, 'snapshotSource')
        ->create(['version' => 1]);
    SnapshotData::factory()
        ->for($from)
        ->create([
            'public_markdown' => $fromMarkdown,
            'private_markdown' => $fromMarkdown,
        ]);

    $to = Snapshot::factory()
        ->recycle($organisation)
        ->for($source, 'snapshotSource')
        ->create(['version' => 2]);
    SnapshotData::factory()
        ->for($to)
        ->create([
            'public_markdown' => $toMarkdown,
            'private_markdown' => $toMarkdown,
        ]);

    return [$from, $to];
}

it('renders the compare page for a source with two versions', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    [, $to] = createComparableSnapshots($source, 'eersteregel oudewaarde', 'eersteregel nieuwewaarde');

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CompareSnapshots::class, [
            'record' => $to->getRouteKey(),
        ])
        ->assertOk()
        ->assertSee('oudewaarde')
        ->assertSee('nieuwewaarde');
});

it('forbids access without the snapshot view permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    [, $to] = createComparableSnapshots($source, 'oud-fragment', 'nieuw-fragment');

    // A user who can see the source entity but lacks SNAPSHOT_VIEW must not be
    // able to read the diff of every snapshot in the tenant.
    $user = UserTestHelper::createForOrganisationWithPermissions(
        $organisation,
        [Permission::CORE_ENTITY_VIEW],
    );

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(CompareSnapshots::class, [
            'record' => $to->getRouteKey(),
        ])
        ->assertForbidden();
});

it('allows access with the snapshot view permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    [, $to] = createComparableSnapshots($source, 'oud-fragment', 'nieuw-fragment');

    $user = UserTestHelper::createForOrganisationWithPermissions(
        $organisation,
        [Permission::CORE_ENTITY_VIEW, Permission::SNAPSHOT_VIEW],
    );

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(CompareSnapshots::class, [
            'record' => $to->getRouteKey(),
        ])
        ->assertOk();
});

it('defaults the pickers to the previous and anchor versions', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    [$from, $to] = createComparableSnapshots($source, 'oud-fragment', 'nieuw-fragment');

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CompareSnapshots::class, [
            'record' => $to->getRouteKey(),
        ])
        ->assertSet('fromId', $from->id->toString())
        ->assertSet('toId', $to->id->toString());
});

it('defaults the from-side to the oldest version when anchored on the oldest', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    [$oldest] = createComparableSnapshots($source, 'oud-fragment', 'nieuw-fragment');

    // Anchoring on the oldest version leaves no earlier version, so the
    // "from" side falls back to the oldest snapshot itself.
    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CompareSnapshots::class, [
            'record' => $oldest->getRouteKey(),
        ])
        ->assertSet('fromId', $oldest->id->toString())
        ->assertSet('toId', $oldest->id->toString());
});

it('renders no diff sections when a selected version is unknown', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    [, $to] = createComparableSnapshots($source, 'oud-fragment', 'nieuw-fragment');

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CompareSnapshots::class, [
            'record' => $to->getRouteKey(),
        ])
        ->set('toId', 'unknown-id')
        ->assertDontSee(__('snapshot.public_data'))
        ->assertDontSee(__('snapshot.private_data'));
});

it('shows a no-changes message when both versions are identical', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    [$to] = createComparableSnapshots($source, 'zelfde-inhoud', 'zelfde-inhoud');

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CompareSnapshots::class, [
            'record' => $to->getRouteKey(),
        ])
        ->assertSee(__('snapshot.compare_no_changes'));
});

it('aborts when the source has fewer than two versions', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($source, 'snapshotSource')
        ->create(['version' => 1]);

    $this->asFilamentOrganisationUser($organisation)
        ->get(SnapshotResource::getUrl('compare', ['record' => $snapshot]))
        ->assertNotFound();
});

it('aborts when the snapshot source has been deleted', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['deleted_at' => fake()->dateTime()]);
    [, $to] = createComparableSnapshots($source, 'oud', 'nieuw');

    $this->asFilamentOrganisationUser($organisation)
        ->get(SnapshotResource::getUrl('compare', ['record' => $to]))
        ->assertNotFound();
});

it('shows a compare action on the view page when comparable versions exist', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    [, $to] = createComparableSnapshots($source, 'oud-fragment', 'nieuw-fragment');

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $to->getRouteKey(),
        ])
        ->assertActionVisible('compare');
});

it('hides the compare action when only one version exists', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($source, 'snapshotSource')
        ->create(['version' => 1]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertActionHidden('compare');
});

it('shows a compare header action in the versions table for comparable versions', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    createComparableSnapshots($source, 'oud', 'nieuw');

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(SnapshotsRelationManager::class, [
            'ownerRecord' => $source,
            'pageClass' => EditAvgResponsibleProcessingRecord::class,
        ])
        ->assertTableActionVisible('compare');
});

it('the compare header action targets the latest version', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    [, $latest] = createComparableSnapshots($source, 'oud', 'nieuw');

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(SnapshotsRelationManager::class, [
            'ownerRecord' => $source,
            'pageClass' => EditAvgResponsibleProcessingRecord::class,
        ])
        ->assertTableActionHasUrl('compare', SnapshotResource::getUrl('compare', ['record' => $latest]));
});

it('shows related entities added between versions', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    // Identical markdown on both sides: the only difference is the attached
    // system, which lives in related_snapshot_sources rather than the markdown.
    [, $to] = createComparableSnapshots($source, 'zelfde-inhoud', 'zelfde-inhoud');

    $system = System::factory()
        ->recycle($organisation)
        ->create(['description' => 'toegevoegd-systeem']);
    RelatedSnapshotSource::factory()->create([
        'snapshot_id' => $to->id,
        'snapshot_source_id' => $system->id,
        'snapshot_source_type' => System::class,
    ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CompareSnapshots::class, [
            'record' => $to->getRouteKey(),
        ])
        ->assertOk()
        ->assertSee(__('snapshot.related_snapshot_sources'))
        ->assertSee(__('system.model_singular'))
        ->assertSee('toegevoegd-systeem');
});

it('shows related entities removed between versions', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    [$from, $to] = createComparableSnapshots($source, 'zelfde-inhoud', 'zelfde-inhoud');

    $system = System::factory()
        ->recycle($organisation)
        ->create(['description' => 'verwijderd-systeem']);
    RelatedSnapshotSource::factory()->create([
        'snapshot_id' => $from->id,
        'snapshot_source_id' => $system->id,
        'snapshot_source_type' => System::class,
    ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CompareSnapshots::class, [
            'record' => $to->getRouteKey(),
        ])
        ->assertOk()
        ->assertSee('verwijderd-systeem');
});

it('reports no changes when both versions share the same related entities', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    [$from, $to] = createComparableSnapshots($source, 'zelfde-inhoud', 'zelfde-inhoud');

    $system = System::factory()
        ->recycle($organisation)
        ->create(['description' => 'ongewijzigd-systeem']);

    foreach ([$from, $to] as $snapshot) {
        RelatedSnapshotSource::factory()->create([
            'snapshot_id' => $snapshot->id,
            'snapshot_source_id' => $system->id,
            'snapshot_source_type' => System::class,
        ]);
    }

    // An unchanged link must not surface as a difference, so the section
    // collapses to the same "no changes" message the markdown sections use.
    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CompareSnapshots::class, [
            'record' => $to->getRouteKey(),
        ])
        ->assertOk()
        ->assertSee(__('snapshot.compare_no_changes'))
        ->assertDontSee('ongewijzigd-systeem');
});

it('hides the compare header action when only one version exists', function (): void {
    $organisation = OrganisationTestHelper::create();
    $source = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    Snapshot::factory()
        ->recycle($organisation)
        ->for($source, 'snapshotSource')
        ->create(['version' => 1]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(SnapshotsRelationManager::class, [
            'ownerRecord' => $source,
            'pageClass' => EditAvgResponsibleProcessingRecord::class,
        ])
        ->assertTableActionHidden('compare');
});
