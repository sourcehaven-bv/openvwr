<?php

declare(strict_types=1);

use App\Events\StaticWebsite\BuildEvent;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\PublicWebsiteTree;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\Obsolete;
use App\Models\StaticWebsiteCheck;
use App\Models\StaticWebsiteSnapshotEntry;
use App\Services\StaticWebsite\Publication\PublicationState;
use App\Services\StaticWebsite\Publication\PublicationStatusResolver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Model\OrganisationTestHelper;

beforeEach(function (): void {
    Event::fake([BuildEvent::class]);
});

/**
 * A record that meets every condition for publication, without a check yet.
 *
 * @return array{AvgResponsibleProcessingRecord, Snapshot, PublicWebsiteTree}
 */
function qualifyingRecord(?Organisation $organisation = null): array
{
    $organisation ??= OrganisationTestHelper::create();
    $tree = PublicWebsiteTree::factory()
        ->for($organisation)
        ->create(['parent_id' => null, 'public_from' => CarbonImmutable::now()->subDay()]);
    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['public_from' => CarbonImmutable::now()->subDay()]);
    $snapshot = Snapshot::factory()
        ->for($record, 'snapshotSource')
        ->recycle($organisation)
        ->withSnapshotData(['forPublicWebsite' => true])
        ->create(['state' => Established::class, 'version' => 1]);

    return [$record, $snapshot, $tree];
}

function confirmOnline(Snapshot $snapshot, string $startDate = '2026-01-01 10:00:00'): StaticWebsiteSnapshotEntry
{
    return StaticWebsiteSnapshotEntry::factory()->create([
        'snapshot_id' => $snapshot->id,
        'url' => 'https://register.test/verwerking/',
        'start_date' => CarbonImmutable::parse($startDate),
        'end_date' => null,
    ]);
}

function resolveStatus(AvgResponsibleProcessingRecord $record): PublicationState
{
    return (new PublicationStatusResolver())->resolve($record->refresh())->state;
}

it('reports a confirmed record as published, with version, date and url', function (): void {
    [$record, $snapshot] = qualifyingRecord();
    confirmOnline($snapshot);

    $status = (new PublicationStatusResolver())->resolve($record);

    expect($status->state)->toBe(PublicationState::PUBLISHED)
        ->and($status->onlineVersion)->toBe(1)
        ->and($status->url)->toBe('https://register.test/verwerking/')
        ->and($status->date?->equalTo(CarbonImmutable::parse('2026-01-01 10:00:00')))->toBeTrue();
});

it('reports a qualifying record without a newer check as pending', function (): void {
    [$record] = qualifyingRecord();

    expect(resolveStatus($record))->toBe(PublicationState::PENDING);
});

it('reports a qualifying record that a later check did not find as not confirmed', function (): void {
    [$record] = qualifyingRecord();
    StaticWebsiteCheck::factory()->create(['build_date' => CarbonImmutable::now()->addMinute()]);

    expect(resolveStatus($record))->toBe(PublicationState::NOT_CONFIRMED);
});

it('reports an older online version as updating', function (): void {
    [$record, $snapshot] = qualifyingRecord();
    $olderSnapshot = Snapshot::factory()
        ->for($record, 'snapshotSource')
        ->recycle($record->getOrganisation())
        ->create(['state' => Obsolete::class, 'version' => 0]);
    confirmOnline($olderSnapshot);

    $status = (new PublicationStatusResolver())->resolve($record);

    expect($status->state)->toBe(PublicationState::UPDATING)
        ->and($status->onlineVersion)->toBe(0)
        ->and($status->establishedVersion)->toBe($snapshot->version);
});

it('reports a future public from date as scheduled', function (): void {
    [$record] = qualifyingRecord();
    $publicFrom = CarbonImmutable::now()->addWeek()->startOfMinute();
    $record->update(['public_from' => $publicFrom]);

    $status = (new PublicationStatusResolver())->resolve($record->refresh());

    expect($status->state)->toBe(PublicationState::SCHEDULED)
        ->and($status->date?->equalTo($publicFrom))->toBeTrue();
});

it('schedules on the latest of the record and website section dates', function (): void {
    [$record, , $tree] = qualifyingRecord();
    // The tree stores a date, not a time.
    $treePublicFrom = CarbonImmutable::now()->addMonth()->startOfDay();
    $tree->update(['public_from' => $treePublicFrom]);

    $status = (new PublicationStatusResolver())->resolve($record->refresh());

    expect($status->state)->toBe(PublicationState::SCHEDULED)
        ->and($status->date?->equalTo($treePublicFrom))->toBeTrue();
});

it('names a missing public from date', function (): void {
    [$record] = qualifyingRecord();
    $record->update(['public_from' => null]);

    expect(resolveStatus($record))->toBe(PublicationState::NOT_PUBLIC_FROM_MISSING);
});

it('names a missing established version', function (): void {
    $organisation = OrganisationTestHelper::create();
    $record = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['public_from' => CarbonImmutable::now()->subDay()]);
    Snapshot::factory()
        ->for($record, 'snapshotSource')
        ->recycle($organisation)
        ->create(['state' => Concept::class, 'version' => 1]);

    $status = (new PublicationStatusResolver())->resolve($record);

    expect($status->state)->toBe(PublicationState::NOT_NO_ESTABLISHED_VERSION)
        ->and($status->getVersionInProgressNotice())->not->toBeNull();
});

it('names an organisation that is not on the website', function (): void {
    [$record, , $tree] = qualifyingRecord();
    $tree->delete();

    expect(resolveStatus($record))->toBe(PublicationState::NOT_ORGANISATION_NOT_ON_WEBSITE);
});

it('names the website section without a public from date', function (): void {
    [$record, , $tree] = qualifyingRecord();
    $parent = PublicWebsiteTree::factory()->create(['parent_id' => null, 'public_from' => null, 'title' => 'Gemeente']);
    $tree->update(['parent_id' => $parent->id]);

    $status = (new PublicationStatusResolver())->resolve($record->refresh());

    expect($status->state)->toBe(PublicationState::NOT_WEBSITE_SECTION_NOT_PUBLIC)
        ->and($status->sectionTitle)->toBe('Gemeente');
});

it('reports an online record that no longer qualifies as removing, with the reason', function (): void {
    [$record, $snapshot] = qualifyingRecord();
    confirmOnline($snapshot);
    $record->update(['public_from' => null]);

    $status = (new PublicationStatusResolver())->resolve($record->refresh());

    expect($status->state)->toBe(PublicationState::REMOVING)
        ->and($status->blocker?->state)->toBe(PublicationState::NOT_PUBLIC_FROM_MISSING)
        ->and($status->url)->not->toBeNull();
});

it('notices a newer version that is not established yet', function (): void {
    [$record, $snapshot] = qualifyingRecord();
    confirmOnline($snapshot);
    Snapshot::factory()
        ->for($record, 'snapshotSource')
        ->recycle($record->getOrganisation())
        ->create(['state' => Concept::class, 'version' => 2]);

    $status = (new PublicationStatusResolver())->resolve($record);

    expect($status->state)->toBe(PublicationState::PUBLISHED)
        ->and($status->versionInProgress?->version)->toBe(2);
});

it('reports publishing disabled', function (): void {
    config()->set('features.publishing', false);
    [$record] = qualifyingRecord();

    expect(resolveStatus($record))->toBe(PublicationState::NOT_PUBLISHING_DISABLED);
});

it('has a label and explanation for every state', function (PublicationState $state): void {
    expect(__(sprintf('static_website.publication_state.%s.label', $state->value)))
        ->not->toStartWith('static_website.')
        ->and(__(sprintf('static_website.publication_state.%s.explanation', $state->value)))
        ->not->toStartWith('static_website.');
})->with(PublicationState::cases());
