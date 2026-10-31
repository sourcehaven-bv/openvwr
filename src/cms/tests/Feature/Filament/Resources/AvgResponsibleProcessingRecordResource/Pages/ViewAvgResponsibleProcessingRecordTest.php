<?php

declare(strict_types=1);

use App\Enums\RegisterLayout;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\ViewAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\PublicWebsiteTree;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use App\Models\StaticWebsiteCheck;
use App\Models\StaticWebsiteSnapshotEntry;
use App\Services\DateFormatService;
use Carbon\CarbonImmutable;
use Tests\Helpers\ConfigTestHelper;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('can load the page with all layouts', function (RegisterLayout $registerLayout): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, ['register_layout' => $registerLayout]);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentUser($user)
        ->get(AvgResponsibleProcessingRecordResource::getUrl('view', [
            'record' => $avgResponsibleProcessingRecord,
        ]))
        ->assertSuccessful();
})->with(RegisterLayout::cases());

// Versions are no longer made by hand, so the view page has no button for it.
it('does not offer a version button', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewAvgResponsibleProcessingRecord::class, ['record' => $avgResponsibleProcessingRecord->id])
        ->assertActionDoesNotExist('snapshot_create');
});

it('shows the link to the static-website page when the record is published', function (): void {
    $publishedAt = fake()->dateTimeBetween('-2 weeks', '-1 week', 'utc');

    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'public_from' => $publishedAt,
        ]);
    Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => Established::class,
        ]);
    $avgResponsibleProcessingRecord->refresh();

    $expectedPublishedAt = CarbonImmutable::instance($publishedAt)
        ->setTimezone(ConfigTestHelper::get('app.display_timezone'))
        ->format(DateFormatService::FORMAT_DATE);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertSee(__('general.published_at', ['published_at' => $expectedPublishedAt]));
});

it('shows current state as not published', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertSee(__('static_website.publication_state.not_no_established_version.label'));
});

it('shows the data of the publications when the record is published', function (): void {
    $organisation = OrganisationTestHelper::create();
    PublicWebsiteTree::factory()
        ->for($organisation)
        ->create(['parent_id' => null, 'public_from' => '2020-01-01']);
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['public_from' => '2020-01-01 00:00:00']);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->recycle($organisation)
        ->withSnapshotData(['forPublicWebsite' => true])
        ->create([
            'state' => Established::class,
        ]);
    $staticWebsiteCheck = StaticWebsiteCheck::factory()
        ->createForSnapshot($snapshot->id);
    StaticWebsiteSnapshotEntry::factory()
        ->create([
            'last_static_website_check_id' => $staticWebsiteCheck->id,
            'snapshot_id' => $snapshot->id,
            'start_date' => '2020-01-01 00:00:00',
            'end_date' => '2020-02-01 00:00:00',
        ]);
    StaticWebsiteSnapshotEntry::factory()
        ->create([
            'last_static_website_check_id' => $staticWebsiteCheck->id,
            'snapshot_id' => $snapshot->id,
            'start_date' => '2020-03-01 00:00:00',
            'end_date' => null,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertSee(__('static_website.publication_state.published.label'))
        ->assertSee(__('static_website.public_from_section.public_history_since', [
            'version' => (string) $snapshot->version,
            'start' => '01-03-2020 01:00',
        ]))
        ->assertSee(__('static_website.public_from_section.public_history_from_to', [
            'version' => (string) $snapshot->version,
            'start' => '01-01-2020 01:00',
            'end' => '01-02-2020 01:00',
        ]));
});

it('shows the subverwerkingen', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $child = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create(['parent_id' => $avgResponsibleProcessingRecord->id->toString()]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertSee(__('general.children'))
        ->assertSee($child->name);
});
