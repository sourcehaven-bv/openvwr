<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Filament\Resources\OrganisationUserResource;
use App\Filament\Resources\OrganisationUserResource\Pages\EditOrganisationUser;
use App\Models\OrganisationUserRole;
use App\Models\User;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the edit page with cpo-manage permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $filamentUser = UserTestHelper::createForOrganisation($organisation);
    $permissions = [
        Permission::USER_CREATE,
        Permission::USER_UPDATE,
        Permission::USER_VIEW,
        Permission::USER_ROLE_GLOBAL_MANAGE,
        Permission::USER_ROLE_ORGANISATION_MANAGE,
        Permission::USER_ROLE_ORGANISATION_CPO_MANAGE,
    ];

    $this->withPermissions($filamentUser, $permissions)
        ->withFilamentSession($filamentUser, $organisation)
        ->get(OrganisationUserResource::getUrl('edit', ['record' => $user]))
        ->assertSuccessful();
});

it('loads the edit page without cpo-manage permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $filamentUser = UserTestHelper::createForOrganisation($organisation);
    $permissions = [
        Permission::USER_CREATE,
        Permission::USER_UPDATE,
        Permission::USER_VIEW,
        Permission::USER_ROLE_GLOBAL_MANAGE,
        Permission::USER_ROLE_ORGANISATION_MANAGE,
    ];

    $this->withPermissions($filamentUser, $permissions)
        ->withFilamentSession($filamentUser, $organisation)
        ->get(OrganisationUserResource::getUrl('edit', ['record' => $user]))
        ->assertSuccessful();
});

it('can edit a role for a user that is already linked to the organisation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $role = fake()->randomElement([
        Role::COUNSELOR,
        Role::DATA_PROTECTION_OFFICIAL,
        Role::INPUT_PROCESSOR,
        Role::MANDATE_HOLDER,
        Role::PRIVACY_OFFICER,
    ]);

    $this->assertDatabaseMissing(OrganisationUserRole::class, [
        'role' => $role->value,
        'user_id' => $user->id,
        'organisation_id' => $organisation->id,
    ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditOrganisationUser::class, ['record' => $user->id])
        ->fillForm([
            $role->value => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();
    $organisationRoles = $user->organisationRoles;
    expect($organisationRoles->count())
        ->toBe(1)
        ->and($organisationRoles->first()->organisation_id)
        ->toBe($organisation->id)
        ->and($organisationRoles->first()->role)
        ->toBe($role);
});

it('can edit a role without assigning any roles', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditOrganisationUser::class, ['record' => $user->id])
        ->fillForm([])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();
    $organisationRoles = $user->organisationRoles;
    expect($organisationRoles->count())
        ->toBe(0);
});

it('can assign inputProcessor without cpoManage permissions', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::USER_ROLE_ORGANISATION_MANAGE,
    ]);

    $userToEdit = UserTestHelper::createForOrganisation($organisation);

    $role = Role::INPUT_PROCESSOR;

    $this->assertDatabaseMissing(OrganisationUserRole::class, [
        'role' => $role->value,
        'user_id' => $userToEdit->id,
        'organisation_id' => $organisation->id,
    ]);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(EditOrganisationUser::class, ['record' => $userToEdit->id])
        ->fillForm([
            $role->value => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $userToEdit->refresh();
    $organisationRoles = $userToEdit->organisationRoles;
    expect($organisationRoles->count())
        ->toBe(1);
});

it('can not assign mandateHolder without cpoManage permissions', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::USER_ROLE_ORGANISATION_MANAGE,
    ]);

    $userToEdit = UserTestHelper::createForOrganisation($organisation);

    $role = Role::MANDATE_HOLDER;

    $this->assertDatabaseMissing(OrganisationUserRole::class, [
        'role' => $role->value,
        'user_id' => $userToEdit->id,
        'organisation_id' => $organisation->id,
    ]);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(EditOrganisationUser::class, ['record' => $userToEdit->id])
        ->fillForm([
            $role->value => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $userToEdit->refresh();
    $organisationRoles = $userToEdit->organisationRoles;
    expect($organisationRoles->count())
        ->toBe(0);
});

it('can detach a user that is linked to an organisation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $role = fake()->randomElement([
        Role::INPUT_PROCESSOR,
        Role::PRIVACY_OFFICER,
        Role::COUNSELOR,
        Role::DATA_PROTECTION_OFFICIAL,
        Role::MANDATE_HOLDER,
    ]);

    $user = User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole($role, $organisation)
        ->create();

    expect($user->organisations->count())
        ->toBe(1);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditOrganisationUser::class, ['record' => $user->id])
        ->callAction('detach')
        ->assertHasNoFormErrors();

    $user->refresh();
    expect($user->organisations->count())
        ->toBe(0);
});
