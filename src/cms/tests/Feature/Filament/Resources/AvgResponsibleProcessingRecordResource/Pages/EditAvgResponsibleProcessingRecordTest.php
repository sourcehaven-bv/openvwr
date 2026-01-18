<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\RegisterLayout;
use App\Filament\Forms\Components\Select\ParentSelect;
use App\Filament\RelationManagers\SnapshotsRelationManager;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\EntityNumber;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Established;
use App\Models\StaticWebsiteCheck;
use App\Models\StaticWebsiteSnapshotEntry;
use App\Models\Tag;
use App\Services\DateFormatService;
use App\Services\EntityNumberService;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\Select;
use Tests\Helpers\ConfigTestHelper;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the edit page with all layouts', function (RegisterLayout $registerLayout): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, ['register_layout' => $registerLayout]);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentUser($user)
        ->get(AvgResponsibleProcessingRecordResource::getUrl('edit', [
            'record' => $avgResponsibleProcessingRecord,
        ]))
        ->assertSuccessful();
})->with(RegisterLayout::cases());

it('can create a snapshot', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    expect($avgResponsibleProcessingRecord->snapshots->count())
        ->toBe(0);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, ['record' => $avgResponsibleProcessingRecord->id])
        ->callAction('snapshot_create')
        ->assertNotified(__('snapshot.created'))
        ->assertDispatched(SnapshotsRelationManager::REFRESH_TABLE_EVENT);

    expect($avgResponsibleProcessingRecord->refresh()->snapshots->count())
        ->toBe(1);
});

it('can be saved', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create([
            'has_security' => false,
            'outside_eu' => false,
            'outside_eu_protection_level' => true,
        ]);
    $name = fake()->uuid();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->fillForm([
            'name' => $name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->name)
        ->toBe($name);
});

it('can be edit & saved with update-permissions for the record, but not for sub-entities', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::CORE_ENTITY_VIEW,
        Permission::CORE_ENTITY_UPDATE,
    ]);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withProcessors()
        ->withResponsibles()
        ->withSystems()
        ->create([
            'has_security' => false,
            'outside_eu' => false,
            'outside_eu_protection_level' => true,
        ]);
    $name = fake()->uuid();

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->fillForm([
            'name' => $name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->name)
        ->toBe($name);
});

it('can see the dpia field', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'geb_dpia_executed' => false,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertSee(__('avg_responsible_processing_record.geb_dpia_automated'));
});

it('can not see the dpia field', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'geb_dpia_executed' => true,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertDontSee(__('avg_responsible_processing_record.geb_dpia_automated'));
});

it('can save a dpia subfield', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create([
            'geb_dpia_executed' => false,
            'geb_dpia_automated' => false,
            'geb_dpia_large_scale_processing' => false,
            'geb_dpia_large_scale_monitoring' => false,
            'geb_dpia_list_required' => false,
            'geb_dpia_criteria_wp248' => false,
            'geb_dpia_high_risk_freedoms' => true,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->fillForm([
            'geb_dpia_criteria_wp248' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->geb_dpia_executed)->toBeFalse()
        ->and($avgResponsibleProcessingRecord->geb_dpia_criteria_wp248)->toBeTrue()
        ->and($avgResponsibleProcessingRecord->geb_dpia_high_risk_freedoms)->toBeFalse();
});

it('can edit the avgResponsibleProcessingRecordService', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $avgResponsibleProcessingRecordService = AvgResponsibleProcessingRecordService::factory()
        ->recycle($organisation)
        ->create([
            'enabled' => true,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->fillForm([
            'avg_responsible_processing_record_service_id' => $avgResponsibleProcessingRecordService->id->toString(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord->refresh();

    expect($avgResponsibleProcessingRecord->avg_responsible_processing_record_service_id->toString())
        ->toBe($avgResponsibleProcessingRecordService->id->toString());
});

it('can see the protection level description field', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'outside_eu' => true,
            'outside_eu_protection_level' => false,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertSee(__('avg_responsible_processing_record.outside_eu_protection_level_description'));
});

it('shows the link to the public page when the record is published', function (): void {
    $publishedAt = fake()->dateTimeBetween('-2 weeks', '-1 week', 'utc');

    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'public_from' => $publishedAt,
        ]);
    Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => Established::class,
        ]);
    $avgResponsibleProcessingRecord->refresh();

    $expectedPublishedAt = CarbonImmutable::instance($publishedAt)
        ->setTimezone(ConfigTestHelper::get('app.display_timezone'))
        ->format(DateFormatService::FORMAT_DATE);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
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
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertSee(__('static_website.public_from_section.public_state_not_public'));
});

it('shows the data of the publications when the record is published', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
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
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertSee(__('static_website.public_from_section.public_state_public'))
        ->assertSee(__('static_website.public_from_section.public_history_since', ['start' => '01-03-2020 00:00']))
        ->assertSee(__('static_website.public_from_section.public_history_from_to', [
            'start' => '01-01-2020 00:00',
            'end' => '01-02-2020 00:00',
        ]));
});

it('shows a parent record from the same organisation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $parentAvgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertFormFieldExists(
            'parent_id',
            static function (ParentSelect $field) use ($parentAvgResponsibleProcessingRecord): bool {
                return $field->getOptions() === [
                    $parentAvgResponsibleProcessingRecord->id->toString() => $parentAvgResponsibleProcessingRecord->name,
                ];
            },
        );
});

it('does not show a parent record from another organisation', function (): void {
    // record from another organisation, should not show up in the form
    AvgResponsibleProcessingRecord::factory()
        ->create();

    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertFormFieldExists('parent_id', static function (ParentSelect $field): bool {
            return $field->getOptions() === [];
        });
});

it('does not create a snapshot on unsaved changes', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->fillForm([
            'name' => 'unsaved change',
        ])
        ->callAction('snapshot_create')
        ->assertNotified(__('snapshot.unsaved_changes'))
        ->assertNotNotified(__('snapshot.created'));
});

it('can be attached to a tag', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create([
            'has_security' => false,
            'outside_eu' => false,
            'outside_eu_protection_level' => true,
        ]);
    $tag = Tag::factory()
        ->recycle($organisation)
        ->create();

    expect($avgResponsibleProcessingRecord->tags->count())
        ->toBe(0);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->fillForm([
            'tags' => [$tag->id->toString()],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->tags->count())
        ->toBe(1);
});

it('can do a lookup for a tag', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $tag = Tag::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->assertFormFieldExists(
            'tags',
            static function (Select $field) use ($tag): bool {
                return $field->getSearchResults($tag->name) === [
                    $tag->id->toString() => $tag->name,
                ];
            },
        );
});

it('shows a (required) validation error for remarks when avg_goal_legal_base selected', function (): void {
    AvgResponsibleProcessingRecord::factory()
        ->create();

    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->fillForm([
            'avgGoals' => [
                0 => [
                    'goal' => null,
                    'avg_goal_legal_base' => fake()->randomElement(__('avg_goal_legal_base.options')),
                    'remarks' => null,
                ],
            ],
        ])
        ->call('save')
        ->assertHasFormErrors(['avgGoals.0.remarks' => 'required']);
});

it('does not allow using a parent from another organisation', function (): void {
    // record from another organisation, should not be possible to use it
    $otherOrganisationAvgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->create();

    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->fillForm([
            'parent_id' => $otherOrganisationAvgResponsibleProcessingRecord->id,
        ])
        ->call('save')
        ->assertHasFormErrors(['parent_id' => 'in']);
});

it('does allow using a parent from another organisation (passing a uuid)', function (): void {
    $organisation = OrganisationTestHelper::create();
    $sameOrganisationAvgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->fillForm([
            'parent_id' => $sameOrganisationAvgResponsibleProcessingRecord->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->parent_id->toString())
        ->toBe($sameOrganisationAvgResponsibleProcessingRecord->id->toString());
});

it('does allow using a parent from another organisation (passing a string)', function (): void {
    $organisation = OrganisationTestHelper::create();
    $sameOrganisationAvgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->fillForm([
            'parent_id' => $sameOrganisationAvgResponsibleProcessingRecord->id->toString(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->parent_id->toString())
        ->toBe($sameOrganisationAvgResponsibleProcessingRecord->id->toString());
});

it('can be cloned', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withAllRelatedEntities()
        ->create();

    $entityNumber = EntityNumber::factory()
        ->create();

    $this->mock(EntityNumberService::class)
        ->shouldReceive('generate')
        ->once()
        ->andReturn($entityNumber);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->getRouteKey(),
        ])
        ->callAction('clone')
        ->assertRedirect();

    $avgResponsibleProcessingRecordClone = AvgResponsibleProcessingRecord::query()
        ->where('entity_number_id', $entityNumber->id)
        ->firstOrFail();

    expect($avgResponsibleProcessingRecordClone->entity_number_id)->not()->toBe($avgResponsibleProcessingRecord->entity_number_id)
        ->and($avgResponsibleProcessingRecordClone->name)->toBe($avgResponsibleProcessingRecord->name)
        ->and($avgResponsibleProcessingRecordClone->public_from?->toJson())->toBe($avgResponsibleProcessingRecord->public_from?->toJson());

    expect($avgResponsibleProcessingRecordClone->fgRemark)->toBeNull();
    expect($avgResponsibleProcessingRecordClone->snapshots)->toBeEmpty();

    expect($avgResponsibleProcessingRecordClone->avgGoals->pluck('id')->toArray())
        ->toEqual($avgResponsibleProcessingRecord->avgGoals->pluck('id')->toArray());

    expect($avgResponsibleProcessingRecordClone->contactPersons->pluck('id')->toArray())
        ->toEqual($avgResponsibleProcessingRecord->contactPersons->pluck('id')->toArray());

    expect($avgResponsibleProcessingRecordClone->dataBreachRecords->pluck('id')->toArray())
        ->toEqual($avgResponsibleProcessingRecord->dataBreachRecords->pluck('id')->toArray());

    expect($avgResponsibleProcessingRecordClone->documents->pluck('id'))
        ->toEqual($avgResponsibleProcessingRecord->documents->pluck('id'));

    expect($avgResponsibleProcessingRecordClone->processors->pluck('id')->toArray())
        ->toEqual($avgResponsibleProcessingRecord->processors->pluck('id')->toArray());

    expect($avgResponsibleProcessingRecordClone->receivers->pluck('id')->toArray())
        ->toEqual($avgResponsibleProcessingRecord->receivers->pluck('id')->toArray());

    expect($avgResponsibleProcessingRecordClone->remarks->pluck('body')->toArray())
        ->toBe($avgResponsibleProcessingRecord->remarks->pluck('body')->toArray());

    expect($avgResponsibleProcessingRecordClone->responsibles->pluck('id')->toArray())
        ->toEqual($avgResponsibleProcessingRecord->responsibles->pluck('id')->toArray());

    expect($avgResponsibleProcessingRecordClone->stakeholders->pluck('id')->toArray())
        ->toEqual($avgResponsibleProcessingRecord->stakeholders->pluck('id')->toArray());

    expect($avgResponsibleProcessingRecordClone->systems->pluck('id')->toArray())
        ->toEqual($avgResponsibleProcessingRecord->systems->pluck('id')->toArray());

    expect($avgResponsibleProcessingRecordClone->tags->pluck('id'))
        ->toEqual($avgResponsibleProcessingRecord->tags->pluck('id'));
});
