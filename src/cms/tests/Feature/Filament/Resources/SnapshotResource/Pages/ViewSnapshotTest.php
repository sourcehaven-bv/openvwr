<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Filament\Infolists\Components\SnapshotStatusChangeAction;
use App\Filament\Infolists\Tabs\Snapshot\ViewInfoTab;
use App\Filament\Resources\SnapshotResource;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\RelatedSnapshotSource;
use App\Models\Responsible;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\SnapshotApprovalLog;
use App\Models\SnapshotData;
use App\Models\SnapshotTransition;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use App\Models\User;
use App\Models\Wpg\WpgProcessingRecord;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

/**
 * The states the single "Status aanpassen" button offers, which is where the
 * per-state permission gating now lives.
 *
 * @return array<string, string>
 */
function transitionOptions(Snapshot $snapshot): array
{
    $method = new ReflectionMethod(SnapshotStatusChangeAction::class, 'getTransitionableStates');

    /** @var array<string, string> $options */
    $options = $method->invoke(null, $snapshot);

    return $options;
}

it('loads the snapshot', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertSee($snapshot->name);
});

it('loads the view page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();
    SnapshotApproval::factory()
        ->create(['snapshot_id' => $snapshot->id]);
    SnapshotApprovalLog::factory()
        ->create(['snapshot_id' => $snapshot->id]);

    $this->asFilamentOrganisationUser($organisation)
        ->get(SnapshotResource::getUrl('view', [
            'record' => $snapshot,
        ]))
        ->assertSuccessful();
});

it('loads the view page if snapshotSource is deleted', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::SNAPSHOT_VIEW,
    ]);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create([
            'deleted_at' => fake()->dateTime(),
        ]);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create();
    SnapshotApproval::factory()
        ->create(['snapshot_id' => $snapshot->id]);
    SnapshotApprovalLog::factory()
        ->create(['snapshot_id' => $snapshot->id]);

    $this->withFilamentSession($user, $organisation)
        ->get(SnapshotResource::getUrl('view', ['record' => $snapshot]))
        ->assertSuccessful();
});

it('displays the snapshot approval count', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();
    SnapshotApproval::factory([
        'snapshot_id' => $snapshot->id,
        'status' => SnapshotApprovalStatus::APPROVED,
    ])->create();
    SnapshotApproval::factory([
        'snapshot_id' => $snapshot->id,
        'status' => SnapshotApprovalStatus::UNKNOWN,
    ])->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertSee('Ondertekeningen')
        ->assertSee('1 / 2');
});

it('can approve a snapshotApproval', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();
    $snapshotApproval = SnapshotApproval::factory()
        ->create([
            'snapshot_id' => $snapshot->id,
            'assigned_to' => $user->id,
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);

    $this->asFilamentUser($user)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshotApproval->snapshot->getRouteKey(),
        ])
        ->mountInfolistAction('snapshot_approval_actions', 'snapshot_approval_approve_action')
        ->callInfolistAction('snapshot_approval_actions', 'snapshot_approval_approve_action', [], ['next' => false]);

    $this->assertDatabaseHas(SnapshotApproval::class, [
        'assigned_to' => $user->id,
        'status' => SnapshotApprovalStatus::APPROVED,
    ]);
});

it('can approve a snapshotApproval and view next', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();
    $snapshotApproval = SnapshotApproval::factory()
        ->for($snapshot)
        ->for($user, 'assignedTo')
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);
    $nextSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();
    SnapshotApproval::factory()
        ->for($nextSnapshot)
        ->for($user, 'assignedTo')
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);

    $this->asFilamentUser($user)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshotApproval->snapshot->getRouteKey(),
        ])
        ->mountInfolistAction('snapshot_approval_actions', 'snapshot_approval_approve_action')
        ->callInfolistAction('snapshot_approval_actions', 'snapshot_approval_approve_action', [], ['next' => true])
        ->assertRedirect(ViewSnapshot::getUrl(['record' => $nextSnapshot]));

    $this->assertDatabaseHas(SnapshotApproval::class, [
        'assigned_to' => $user->id,
        'status' => SnapshotApprovalStatus::APPROVED,
    ]);
});

it('does not select a next snapshot from another organisation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $otherOrganisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $current = Snapshot::factory()->for($organisation)->create();
    $otherSnapshot = Snapshot::factory()->for($otherOrganisation)->create();

    SnapshotApproval::factory()
        ->for($otherSnapshot)
        ->for($user, 'assignedTo')
        ->create(['status' => SnapshotApprovalStatus::UNKNOWN]);

    $this->asFilamentOrganisationUser($organisation);

    expect(ViewSnapshot::getNext($current))->toBeNull();
});

it('can decline a snapshotApproval', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();
    $snapshotApproval = SnapshotApproval::factory()
        ->create([
            'snapshot_id' => $snapshot->id,
            'assigned_to' => $user->id,
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);

    $this->asFilamentUser($user)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshotApproval->snapshot->getRouteKey(),
        ])
        ->mountInfolistAction('snapshot_approval_actions', 'snapshot_approval_decline_action')
        ->callInfolistAction('snapshot_approval_actions', 'snapshot_approval_decline_action', [], ['next' => false]);

    $this->assertDatabaseHas(SnapshotApproval::class, [
        'assigned_to' => $user->id,
        'status' => SnapshotApprovalStatus::DECLINED,
    ]);
});

it('can decline a snapshotApproval and view next', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();
    $snapshotApproval = SnapshotApproval::factory()
        ->for($snapshot)
        ->for($user, 'assignedTo')
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);
    $nextSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();
    SnapshotApproval::factory()
        ->for($nextSnapshot)
        ->for($user, 'assignedTo')
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);

    $this->asFilamentUser($user)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshotApproval->snapshot->getRouteKey(),
        ])
        ->mountInfolistAction('snapshot_approval_actions', 'snapshot_approval_decline_action')
        ->callInfolistAction('snapshot_approval_actions', 'snapshot_approval_decline_action', [], ['next' => true])
        ->assertRedirect(ViewSnapshot::getUrl(['record' => $nextSnapshot]));

    $this->assertDatabaseHas(SnapshotApproval::class, [
        'assigned_to' => $user->id,
        'status' => SnapshotApprovalStatus::DECLINED,
    ]);
});

it('can transition to a new state with all approved', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);
    SnapshotApproval::factory()->create([
        'snapshot_id' => $snapshot->id,
        'status' => SnapshotApprovalStatus::APPROVED,
    ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->callInfolistAction(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_status_change', data: [
            'state' => Approved::$name,
        ]);

    $this->assertDatabaseHas(Snapshot::class, [
        'state' => Approved::$name,
    ]);
});

it('can transition to a new state with not all approved', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);
    SnapshotApproval::factory()->create([
        'snapshot_id' => $snapshot->id,
        'status' => SnapshotApprovalStatus::DECLINED,
    ]);
    SnapshotApproval::factory()->create([
        'snapshot_id' => $snapshot->id,
        'status' => SnapshotApprovalStatus::APPROVED,
    ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->callInfolistAction(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_status_change', data: [
            'state' => Approved::$name,
        ]);

    $this->assertDatabaseHas(Snapshot::class, [
        'state' => Approved::$name,
    ]);
});

// One button now, next to the status flow: the reachable states are the options it
// offers rather than separate buttons in the page header.
it('offers every reachable state as an option', function (string $currentState, string $expectedState): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => $currentState,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertInfolistActionVisible(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_status_change');

    expect(transitionOptions($snapshot))
        ->toHaveKey($expectedState);
})->with([
    [InReview::$name, Approved::$name],
    [Approved::$name, Established::$name],
    [InReview::$name, Obsolete::$name],
    [Approved::$name, Obsolete::$name],
    [Established::$name, Obsolete::$name],
]);

// A concept is not moved along from here: it is submitted from the record's own form,
// which is the only place its required fields can be filled in.
it('offers no status change on a concept', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => Concept::class,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertInfolistActionHidden(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_status_change');

    expect(transitionOptions($snapshot))
        ->toBe([]);
});

it('does not display approval-data if none given', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertDontSee(__('snapshot_approval.reviewed_at'));
});

it('can render the page if snapshot has no snapshot-data', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertOk();
});

it('can render the page if snapshot-data has no public_markdown', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();
    SnapshotData::factory()
        ->for($snapshot)
        ->create([
            'public_markdown' => null,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertOk();
});

it('can render related entities in public markdown', function (): void {
    $repsonsiblePublicMarkdown = fake()->sentence();
    $avgResponsibleProcessingRecordPublicMarkdown = fake()->sentence();

    $organisation = OrganisationTestHelper::create();
    $responsible = Responsible::factory()
        ->recycle($organisation)
        ->create();
    $responsibleSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($responsible, 'snapshotSource')
        ->create([
            'state' => Established::class,
        ]);
    SnapshotData::factory()
        ->for($responsibleSnapshot)
        ->create([
            'public_markdown' => $repsonsiblePublicMarkdown,
        ]);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $avgResponsibleProcessingRecordSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => Established::class,
        ]);
    SnapshotData::factory()
        ->for($avgResponsibleProcessingRecordSnapshot)
        ->create([
            'public_markdown' => sprintf('%s <!--- #App\Models\Responsible# --->', $avgResponsibleProcessingRecordPublicMarkdown),
            'private_markdown' => fake()->markdown(),
        ]);

    RelatedSnapshotSource::factory()
        ->create([
            'snapshot_id' => $avgResponsibleProcessingRecordSnapshot->id,
            'snapshot_source_id' => $responsible->id,
            'snapshot_source_type' => $responsible::class,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $avgResponsibleProcessingRecordSnapshot->getRouteKey(),
        ])
        ->assertSee([$avgResponsibleProcessingRecordPublicMarkdown, $repsonsiblePublicMarkdown]);
});

it('will not render related entities without public markdown', function (): void {
    $avgResponsibleProcessingRecordPublicMarkdown = fake()->sentence();

    $organisation = OrganisationTestHelper::create();
    $responsible = Responsible::factory()
        ->recycle($organisation)
        ->create();
    $responsibleSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($responsible, 'snapshotSource')
        ->create([
            'state' => Established::class,
        ]);
    SnapshotData::factory()
        ->for($responsibleSnapshot)
        ->create([
            'public_markdown' => null,
        ]);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $avgResponsibleProcessingRecordSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => Established::class,
        ]);
    SnapshotData::factory()
        ->for($avgResponsibleProcessingRecordSnapshot)
        ->create([
            'public_markdown' => sprintf('%s <!--- #App\Models\Responsible# --->', $avgResponsibleProcessingRecordPublicMarkdown),
        ]);

    RelatedSnapshotSource::factory()
        ->create([
            'snapshot_id' => $avgResponsibleProcessingRecordSnapshot->id,
            'snapshot_source_id' => $responsible->id,
            'snapshot_source_type' => $responsible::class,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $avgResponsibleProcessingRecordSnapshot->getRouteKey(),
        ])
        ->assertSee([$avgResponsibleProcessingRecordPublicMarkdown]);
});

it('will not fail if no template is specified for a related entity', function (): void {
    $wpgProcessingRecordPublicMarkdown = fake()->sentence();
    $avgResponsibleProcessingRecordPublicMarkdown = fake()->sentence();

    $organisation = OrganisationTestHelper::create();
    $wpgProcessingRecord = WpgProcessingRecord::factory() // assuming this will never be related to an avg-record, I can abuse it here
        ->recycle($organisation)
        ->create();
    $wpgProcessingRecordSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($wpgProcessingRecord, 'snapshotSource')
        ->create([
            'state' => Established::class,
        ]);
    SnapshotData::factory()
        ->for($wpgProcessingRecordSnapshot)
        ->create([
            'public_markdown' => $wpgProcessingRecordPublicMarkdown,
        ]);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();
    $avgResponsibleProcessingRecordSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->for($avgResponsibleProcessingRecord, 'snapshotSource')
        ->create([
            'state' => Established::class,
        ]);
    SnapshotData::factory()
        ->for($avgResponsibleProcessingRecordSnapshot)
        ->create([
            'public_markdown' => sprintf(
                '%s <!--- #App\Models\Wpg\WpgProcessingRecord# --->',
                $avgResponsibleProcessingRecordPublicMarkdown,
            ),
        ]);

    RelatedSnapshotSource::factory()
        ->create([
            'snapshot_id' => $avgResponsibleProcessingRecordSnapshot->id,
            'snapshot_source_id' => $wpgProcessingRecord->id,
            'snapshot_source_type' => $wpgProcessingRecord::class,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $avgResponsibleProcessingRecordSnapshot->getRouteKey(),
        ])
        ->assertSee([$avgResponsibleProcessingRecordPublicMarkdown])
        ->assertDontSee([$wpgProcessingRecordPublicMarkdown]);
});

it('can export to pdf', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->withSnapshotData()
        ->create([
            'state' => InReview::class,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->callAction('export_to_pdf')
        ->assertFileDownloaded();
});

it('can not export to pdf is no snapshot-data available', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertActionDisabled('export_to_pdf');
});

it('has a next button if available', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);
    SnapshotApproval::factory()
        ->for($snapshot)
        ->for($user, 'assignedTo')
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);
    $nextSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);
    SnapshotApproval::factory()
        ->for($nextSnapshot)
        ->for($user, 'assignedTo')
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);

    $this->asFilamentUser($user)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertActionEnabled('approve_view_next');
});

it('has no next button if none available', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);
    SnapshotApproval::factory()
        ->for($snapshot)
        ->for($user, 'assignedTo')
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);

    $this->asFilamentUser($user)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertActionDisabled('approve_view_next');
});

it('has no next button if no permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [Permission::SNAPSHOT_VIEW]);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);
    SnapshotApproval::factory()
        ->for($snapshot)
        ->for($user, 'assignedTo')
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);
    $nextSnapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);
    SnapshotApproval::factory()
        ->for($nextSnapshot)
        ->for($user, 'assignedTo')
        ->create([
            'status' => SnapshotApprovalStatus::UNKNOWN,
        ]);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertActionDisabled('approve_view_next');
});

it('shows button to view all snapshot approvals', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertActionEnabled('approve_view_all');
});

it('does not show button to view all snapshot approvales if no permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [Permission::SNAPSHOT_VIEW]);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertActionDisabled('approve_view_all');
});

it('shows the status flow with the reached states and who reached them', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = User::factory()->create(['name' => 'Statuswijziger']);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => Established::class,
        ]);

    // A recorded happy-path history: reached In review, Goedgekeurd and
    // Vastgesteld, the last of which is the current state.
    foreach ([InReview::class, Approved::class, Established::class] as $state) {
        SnapshotTransition::factory()
            ->recycle($snapshot)
            ->recycle($user)
            ->create(['state' => $state]);
    }

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertSee(__('snapshot.status_flow'))
        ->assertSeeInOrder([
            __('snapshot_state.label.in_review'),
            __('snapshot_state.label.approved'),
            __('snapshot_state.label.established'),
        ])
        ->assertSee('Statuswijziger');
});

it('shows the obsolete branch in the status flow when a snapshot is obsolete', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => Obsolete::class,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertSee(__('snapshot_state.label.obsolete'));
});

it('offers a forward skip transition when the user has the target permission', function (): void {
    // Target-only gating: the established permission alone unlocks the skip from
    // review, without the (bypassed) approve permission.
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::SNAPSHOT_VIEW,
        Permission::SNAPSHOT_STATE_TO_ESTABLISHED,
    ]);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertInfolistActionVisible(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_status_change');

    expect(transitionOptions($snapshot))
        ->toHaveKey(Established::$name);
});

it('hides a forward skip transition when the user lacks the target permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::SNAPSHOT_VIEW,
        Permission::SNAPSHOT_STATE_TO_APPROVE,
    ]);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ]);

    expect(transitionOptions($snapshot))
        ->not->toHaveKey(Established::$name);
});

it('establishes straight from review when the skip transition is triggered from the page', function (): void {
    // Target-only gating: establishing from review needs only the established
    // permission, not the bypassed approve permission.
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::SNAPSHOT_VIEW,
        Permission::SNAPSHOT_STATE_TO_ESTABLISHED,
    ]);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->callInfolistAction(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_status_change', data: [
            'state' => Established::$name,
        ]);

    expect($snapshot->refresh()->state)->toBeInstanceOf(Established::class);

    // The bypassed approval step is not recorded: only established.
    $recordedStates = SnapshotTransition::where('snapshot_id', $snapshot->id)
        ->get()
        ->map(static fn (SnapshotTransition $transition): string => $transition->state::$name)
        ->all();
    expect($recordedStates)
        ->toContain(Established::$name)
        ->not->toContain(Approved::$name);
});

it('replaces the transition button with the next one after transitioning', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);
    SnapshotApproval::factory()->create([
        'snapshot_id' => $snapshot->id,
        'status' => SnapshotApprovalStatus::APPROVED,
    ]);

    // The status action dispatches the refresh event that re-renders the page, so
    // the button offers the onward states instead of the spent "Goedkeuren".
    $test = $this->asFilamentOrganisationUser($organisation);

    expect(transitionOptions($snapshot))
        ->toHaveKey(Approved::$name);

    $test->createLivewireTestable(ViewSnapshot::class, [
        'record' => $snapshot->getRouteKey(),
    ])
        ->callInfolistAction(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_status_change', data: [
            'state' => Approved::$name,
        ])
        ->assertDispatched(ViewSnapshot::REFRESH_LIVEWIRE_COMPONENT);

    expect(transitionOptions($snapshot->refresh()))
        ->not->toHaveKey(Approved::$name)
        ->toHaveKey(Established::$name);
});

it('marks a bypassed station as skipped in the status flow', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = User::factory()->create(['name' => 'Statuswijziger']);
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => Established::class,
        ]);

    // History of a direct establish-from-review: only In review and Vastgesteld
    // were recorded, Goedgekeurd was skipped.
    foreach ([InReview::class, Established::class] as $state) {
        SnapshotTransition::factory()
            ->recycle($snapshot)
            ->recycle($user)
            ->create(['state' => $state]);
    }

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertSee(__('snapshot_state.label.approved'))
        ->assertSee(__('snapshot.status_flow_skipped'));
});

it('does not mark a not-yet-reached station as skipped', function (): void {
    $organisation = OrganisationTestHelper::create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create([
            'state' => InReview::class,
        ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ViewSnapshot::class, [
            'record' => $snapshot->getRouteKey(),
        ])
        ->assertDontSee(__('snapshot.status_flow_skipped'));
});
