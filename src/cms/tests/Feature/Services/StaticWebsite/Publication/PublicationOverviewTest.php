<?php

declare(strict_types=1);

use App\Events\StaticWebsite\BuildEvent;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\PublicWebsiteTree;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use App\Models\StaticWebsiteCheck;
use App\Models\StaticWebsiteSnapshotEntry;
use App\Services\StaticWebsite\Publication\PublicationOverview;
use App\Services\StaticWebsite\Publication\PublicationState;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Model\OrganisationTestHelper;

beforeEach(function (): void {
    Event::fake([BuildEvent::class]);
});

function publishableRecordUnder(PublicWebsiteTree $tree): Snapshot
{
    $organisation = OrganisationTestHelper::create();
    $tree->update(['organisation_id' => $organisation->id]);
    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['public_from' => CarbonImmutable::now()->subDay()]);

    return Snapshot::factory()
        ->for($record, 'snapshotSource')
        ->recycle($organisation)
        ->withSnapshotData(['forPublicWebsite' => true])
        ->create(['state' => Established::class]);
}

it('lists qualifying records the last check did not confirm, across the public tree', function (): void {
    $root = PublicWebsiteTree::factory()->create(['parent_id' => null, 'public_from' => '2026-01-01', 'organisation_id' => null]);
    $child = PublicWebsiteTree::factory()->create(['parent_id' => $root->id, 'public_from' => '2026-01-01', 'organisation_id' => null]);
    $hiddenChild = PublicWebsiteTree::factory()->create(['parent_id' => $root->id, 'public_from' => null, 'organisation_id' => null]);

    $confirmed = publishableRecordUnder($root);
    $unconfirmed = publishableRecordUnder($child);
    publishableRecordUnder($hiddenChild);

    StaticWebsiteCheck::factory()->create(['build_date' => CarbonImmutable::now()->addMinute()]);
    StaticWebsiteSnapshotEntry::factory()->create(['snapshot_id' => $confirmed->id, 'end_date' => null]);

    $overview = app(PublicationOverview::class);
    $items = $overview->getUnconfirmedRecords();

    expect($overview->getConfirmedCount())->toBe(1)
        ->and($items)->toHaveCount(1)
        ->and($items[0]['record']->getKey())->toEqual($unconfirmed->snapshot_source_id)
        ->and($items[0]['status']->state)->toBe(PublicationState::NOT_CONFIRMED);
});
