<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Filament\Resources\OrganisationUserResource;
use App\Models\OrganisationUserRole;
use App\Models\User;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the list page', function (): void {
    $this->asFilamentUser()
        ->get(OrganisationUserResource::getUrl())
        ->assertSuccessful();
});

it('loads the list page with role-data', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $role = fake()->randomElement(Role::cases());

    UserTestHelper::createForOrganisation($organisation);
    OrganisationUserRole::factory()
        ->for($user)
        ->for($organisation)
        ->create([
            'role' => $role,
        ]);

    $this->asFilamentUser($user)
        ->get(OrganisationUserResource::getUrl())
        ->assertSuccessful()
        ->assertSee(__(sprintf('role.%s', $role->value)));
});


it('scopes the user resource while keeping shared identities independent of the tenant', function (): void {
    $organisation = OrganisationTestHelper::create();
    $otherOrganisation = OrganisationTestHelper::create();
    $foreignUser = UserTestHelper::createForOrganisation($otherOrganisation);

    $this->asFilamentOrganisationUser($organisation);

    expect(OrganisationUserResource::getEloquentQuery()->whereKey($foreignUser->id)->exists())->toBeFalse()
        ->and(User::query()->whereKey($foreignUser->id)->exists())->toBeTrue();

    $this->get(OrganisationUserResource::getUrl('edit', ['record' => $foreignUser]))
        ->assertNotFound();

    $newUser = User::factory()->create();

    expect($newUser->organisations()->exists())->toBeFalse();
});
