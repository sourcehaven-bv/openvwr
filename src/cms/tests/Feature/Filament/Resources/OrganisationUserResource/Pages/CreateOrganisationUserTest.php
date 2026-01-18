<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Enums\Authorization\Role;
use App\Filament\Resources\OrganisationUserResource;
use App\Filament\Resources\OrganisationUserResource\Pages\CreateOrganisationUser;
use App\Models\OrganisationUserRole;
use App\Models\User;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the create page with cpo-manage permission', function (): void {
    $organisation = OrganisationTestHelper::create();
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
        ->get(OrganisationUserResource::getUrl('create'))
        ->assertSuccessful();
});

it('loads the create page without cpo-manage permission', function (): void {
    $organisation = OrganisationTestHelper::create();
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
        ->get(OrganisationUserResource::getUrl('create'))
        ->assertSuccessful();
});

it('loads the create page without user-manage permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $filamentUser = UserTestHelper::createForOrganisation($organisation);
    $permissions = [
        Permission::USER_ROLE_ORGANISATION_MANAGE,
    ];

    $this->withPermissions($filamentUser, $permissions)
        ->withFilamentSession($filamentUser, $organisation)
        ->get(OrganisationUserResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create a new user without cpo-permission & without assigning any roles', function (): void {
    $organisation = OrganisationTestHelper::create([
        'allowed_email_domains' => [],
    ]);
    $user = UserTestHelper::createForOrganisationWithPermissions($organisation, [
        Permission::USER_ROLE_ORGANISATION_MANAGE,
    ]);

    $email = fake()->unique()->safeEmail();
    $this->assertDatabaseMissing(User::class, [
        'email' => $email,
    ]);

    $this->withFilamentSession($user, $organisation)
        ->createLivewireTestable(CreateOrganisationUser::class)
        ->fillForm([
            'email' => $email,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', $email)->firstOrFail();
    $organisationRoles = $user->organisationRoles;

    expect($organisationRoles->count())
        ->toBe(0);
});

it('can create a new user with cpo-manage permission', function (): void {
    $organisation = OrganisationTestHelper::create([
        'allowed_email_domains' => [],
    ]);

    $email = fake()->unique()->safeEmail();
    $role = fake()->randomElement([
        Role::INPUT_PROCESSOR,
        Role::PRIVACY_OFFICER,
        Role::COUNSELOR,
        Role::DATA_PROTECTION_OFFICIAL,
        Role::MANDATE_HOLDER,
        Role::CHIEF_PRIVACY_OFFICER,
    ]);

    $this->assertDatabaseMissing(User::class, [
        'email' => $email,
    ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateOrganisationUser::class)
        ->fillForm([
            'email' => $email,
            $role->value => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', $email)->firstOrFail();
    $organisationRoles = $user->organisationRoles;

    expect($organisationRoles->count())
        ->toBe(1)
        ->and($organisationRoles->first()->organisation_id)
        ->toBe($organisation->id)
        ->and($organisationRoles->first()->role)
        ->toBe($role);
});

it('can add a role for an new user', function (): void {
    $allowedEmailDomain = fake()->unique()->domainName();
    $email = sprintf('%s@%s', fake()->unique()->userName(), $allowedEmailDomain);
    $role = fake()->randomElement([
        Role::INPUT_PROCESSOR,
        Role::PRIVACY_OFFICER,
        Role::COUNSELOR,
        Role::DATA_PROTECTION_OFFICIAL,
        Role::MANDATE_HOLDER,
        Role::CHIEF_PRIVACY_OFFICER,
    ]);

    $organisation = OrganisationTestHelper::create([
        'allowed_email_domains' => [$allowedEmailDomain],
    ]);
    $filamentUser = UserTestHelper::createForOrganisation($organisation);
    $permissions = [
        Permission::USER_ROLE_ORGANISATION_MANAGE,
        Permission::USER_ROLE_ORGANISATION_CPO_MANAGE,
    ];

    $this->withPermissions($filamentUser, $permissions)
        ->withFilamentSession($filamentUser, $organisation)
        ->createLivewireTestable(CreateOrganisationUser::class)
        ->fillForm([
            'email' => $email,
            $role->value => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', $email)->firstOrFail();
    $organisationRoles = $user->organisationRoles;

    expect($organisationRoles->count())
        ->toBe(1)
        ->and($organisationRoles->first()->organisation_id)
        ->toBe($organisation->id)
        ->and($organisationRoles->first()->role)
        ->toBe($role);
});

it('can add a role for an new user without cpo-manage permission', function (): void {
    $allowedEmailDomain = fake()->unique()->domainName();
    $email = sprintf('%s@%s', fake()->unique()->userName(), $allowedEmailDomain);
    $role = fake()->randomElement([
        Role::INPUT_PROCESSOR,
        Role::PRIVACY_OFFICER,
        Role::COUNSELOR,
        Role::DATA_PROTECTION_OFFICIAL,
    ]);

    $organisation = OrganisationTestHelper::create([
        'allowed_email_domains' => [$allowedEmailDomain],
    ]);
    $filamentUser = UserTestHelper::createForOrganisation($organisation);
    $permissions = [
        Permission::USER_ROLE_ORGANISATION_MANAGE,
    ];

    $this->withPermissions($filamentUser, $permissions)
        ->withFilamentSession($filamentUser, $organisation)
        ->createLivewireTestable(CreateOrganisationUser::class)
        ->fillForm([
            'email' => $email,
            $role->value => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', $email)->firstOrFail();
    $organisationRoles = $user->organisationRoles;

    expect($organisationRoles->count())
        ->toBe(1)
        ->and($organisationRoles->first()->organisation_id)
        ->toBe($organisation->id)
        ->and($organisationRoles->first()->role)
        ->toBe($role);
});

it('can not add a role for a cpo when user has no cpo-manage permission', function (): void {
    $allowedEmailDomain = fake()->unique()->domainName();
    $email = sprintf('%s@%s', fake()->unique()->userName(), $allowedEmailDomain);
    $role = fake()->randomElement([
        Role::MANDATE_HOLDER,
        Role::CHIEF_PRIVACY_OFFICER,
    ]);

    $organisation = OrganisationTestHelper::create([
        'allowed_email_domains' => [$allowedEmailDomain],
    ]);
    $filamentUser = UserTestHelper::createForOrganisation($organisation);
    $permissions = [
        Permission::USER_ROLE_ORGANISATION_MANAGE,
    ];

    $this->withPermissions($filamentUser, $permissions)
        ->withFilamentSession($filamentUser, $organisation)
        ->createLivewireTestable(CreateOrganisationUser::class)
        ->fillForm([
            'email' => $email,
            $role->value => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', $email)->firstOrFail();
    $organisationRoles = $user->organisationRoles;

    expect($organisationRoles->count())
        ->toBe(0);
});

it('can add a role for an new user if domain is allowed', function (): void {
    $allowedEmailDomain = fake()->unique()->domainName();
    $email = sprintf('%s@%s', fake()->unique()->userName(), $allowedEmailDomain);
    $role = fake()->randomElement([
        Role::INPUT_PROCESSOR,
        Role::PRIVACY_OFFICER,
        Role::COUNSELOR,
        Role::DATA_PROTECTION_OFFICIAL,
        Role::MANDATE_HOLDER,
        Role::CHIEF_PRIVACY_OFFICER,
    ]);

    $this->assertDatabaseMissing(User::class, [
        'email' => $email,
    ]);

    $organisation = OrganisationTestHelper::create([
        'allowed_email_domains' => [$allowedEmailDomain],
    ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateOrganisationUser::class)
        ->fillForm([
            'email' => $email,
            $role->value => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', $email)->firstOrFail();
    $organisationRoles = $user->organisationRoles;

    expect($organisationRoles->count())
        ->toBe(1)
        ->and($organisationRoles->first()->organisation_id)
        ->toBe($organisation->id)
        ->and($organisationRoles->first()->role)
        ->toBe($role);
});

it('can not add a role for an new user if domain is not allowed', function (): void {
    $email = sprintf('%s@%s', fake()->unique()->userName(), fake()->unique()->domainName());
    $role = fake()->randomElement([
        Role::INPUT_PROCESSOR,
        Role::PRIVACY_OFFICER,
        Role::COUNSELOR,
        Role::DATA_PROTECTION_OFFICIAL,
        Role::MANDATE_HOLDER,
        Role::CHIEF_PRIVACY_OFFICER,
    ]);

    $organisation = OrganisationTestHelper::create([
        'allowed_email_domains' => [fake()->unique()->domainName()],
    ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateOrganisationUser::class)
        ->fillForm([
            'email' => $email,
            $role->value => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['email']);
});

it('can add a role for an existing user that is not yet linked to the organisation', function (): void {
    $user = User::factory()
        ->create();
    $role = fake()->randomElement([
        Role::INPUT_PROCESSOR,
        Role::PRIVACY_OFFICER,
        Role::COUNSELOR,
        Role::DATA_PROTECTION_OFFICIAL,
        Role::MANDATE_HOLDER,
        Role::CHIEF_PRIVACY_OFFICER,
    ]);

    $organisation = OrganisationTestHelper::create([
        'allowed_email_domains' => [],
    ]);

    $this->assertDatabaseMissing(OrganisationUserRole::class, [
        'role' => $role->value,
        'user_id' => $user->id,
        'organisation_id' => $organisation->id,
    ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateOrganisationUser::class)
        ->fillForm([
            'email' => $user->email,
            $role->value => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(OrganisationUserRole::class, [
        'role' => $role->value,
        'user_id' => $user->id,
        'organisation_id' => $organisation->id,
    ]);

    $user->refresh();
    $organisationRoles = $user->organisationRoles;
    expect($organisationRoles->count())
        ->toBe(1)
        ->and($organisationRoles->first()->organisation_id)
        ->toBe($organisation->id)
        ->and($organisationRoles->first()->role)
        ->toBe($role);
});

it('can not add a role for an existing user if domain is not allowed', function (): void {
    $email = sprintf('%s@%s', fake()->unique()->userName(), fake()->unique()->domainName());

    $user = User::factory()
        ->create([
            'email' => $email,
        ]);
    $role = fake()->randomElement([
        Role::INPUT_PROCESSOR,
        Role::PRIVACY_OFFICER,
        Role::COUNSELOR,
        Role::DATA_PROTECTION_OFFICIAL,
        Role::MANDATE_HOLDER,
        Role::CHIEF_PRIVACY_OFFICER,
    ]);

    $organisation = OrganisationTestHelper::create([
        'allowed_email_domains' => [fake()->unique()->domainName()],
    ]);

    $this->assertDatabaseMissing(OrganisationUserRole::class, [
        'role' => $role->value,
        'user_id' => $user->id,
        'organisation_id' => $organisation->id,
    ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateOrganisationUser::class)
        ->fillForm([
            'email' => $user->email,
            $role->value => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['email']);
});

it('can not a role for an existing user that is not already linked to the organisation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = User::factory()
        ->hasAttached($organisation)
        ->create();

    $role = fake()->randomElement([
        Role::INPUT_PROCESSOR,
        Role::PRIVACY_OFFICER,
        Role::COUNSELOR,
        Role::DATA_PROTECTION_OFFICIAL,
        Role::MANDATE_HOLDER,
        Role::CHIEF_PRIVACY_OFFICER,
    ]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateOrganisationUser::class)
        ->fillForm([
            'email' => $user->email,
            $role->value => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['email']);
});
