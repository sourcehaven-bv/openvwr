<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Livewire\Snapshot\Approvals;
use App\Models\Snapshot;
use App\Models\SnapshotApproval;
use App\Models\User;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('can request a mandateholder as reviewer', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::SNAPSHOT_APPROVAL_CREATE,
    ]);
    $approvalRequestUser = User::factory()
        ->hasOrganisationRole(Role::MANDATE_HOLDER, $organisation)
        ->hasAttached($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(Approvals::class, [
            'snapshot' => $snapshot,
        ])
        ->mountTableAction('snapshot_approval_request_action')
        ->callTableAction('snapshot_approval_request_action', $snapshot, [
            'user_ids' => [$approvalRequestUser->id],
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas(SnapshotApproval::class, [
        'assigned_to' => $approvalRequestUser->id,
    ]);
});

it('can not request a non-mandateholder as reviewer', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::SNAPSHOT_APPROVAL_CREATE,
    ]);
    $approvalRequestUser = User::factory()
        ->hasAttached($organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(Approvals::class, [
            'snapshot' => $snapshot,
        ])
        ->mountTableAction('snapshot_approval_request_action')
        ->callTableAction('snapshot_approval_request_action', $snapshot, [
            'user_ids' => [$approvalRequestUser->id],
        ])
        ->assertHasTableActionErrors(['user_ids']);
});

it('can not request a mandateholder from another organisation as reviewer', function (): void {
    $organisation = OrganisationTestHelper::create();
    $otherOrganisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::SNAPSHOT_APPROVAL_CREATE,
    ]);
    $approvalRequestUser = User::factory()
        ->hasOrganisationRole(Role::MANDATE_HOLDER, $otherOrganisation)
        ->hasAttached($otherOrganisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(Approvals::class, [
            'snapshot' => $snapshot,
        ])
        ->mountTableAction('snapshot_approval_request_action')
        ->callTableAction('snapshot_approval_request_action', $snapshot, [
            'user_ids' => [$approvalRequestUser->id],
        ])
        ->assertHasTableActionErrors(['user_ids']);
});

it('can delete a reviewer', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::SNAPSHOT_APPROVAL_DELETE,
    ]);
    $approvalRequestUser = User::factory()
        ->hasOrganisationRole(Role::MANDATE_HOLDER, $organisation)
        ->create();
    $snapshot = Snapshot::factory()
        ->recycle($organisation)
        ->create();
    $snapshotApproval = SnapshotApproval::factory()->create([
        'snapshot_id' => $snapshot->id,
        'assigned_to' => $approvalRequestUser->id,
        'notified_at' => null,
    ]);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(Approvals::class, [
            'snapshot' => $snapshot,
        ])
        ->callTableBulkAction('snapshot_approval_notify_bulk_delete', [$snapshotApproval], ['user_ids' => [$approvalRequestUser->id]])
        ->assertHasNoTableBulkActionErrors();

    $this->assertDatabaseMissing(SnapshotApproval::class, [
        'assigned_to' => $approvalRequestUser->id,
    ]);
});
