<?php

declare(strict_types=1);

use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Filament\Resources\SnapshotResource;
use App\Filament\Resources\SnapshotResource\Pages\CompareSnapshots;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Snapshot;
use App\Models\SnapshotData;
use Tests\Helpers\Model\OrganisationTestHelper;

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
