<?php

declare(strict_types=1);

use App\Models\User;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation)
        ->get(sprintf('%s/profile', $organisation->slug))
        ->assertSee(__('user.profile.my_profile'));
});

it('returns 404 on tenant/profile if no organisation attached', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = User::factory()->create();

    $this->asFilamentUser($user)
        ->get(sprintf('%s/profile', $organisation->slug))
        ->assertNotFound();
});

it('returns 404 on /profile if no organisation attached', function (): void {
    $user = User::factory()->create();

    $this->be($user)
        ->get('/profile')
        ->assertNotFound();
});

it('offers OTP enrolment and no proxy links under the builtin driver', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $user->organisations()->attach(OrganisationTestHelper::create());

    $this->asFilamentUser($user)
        ->get(sprintf('%s/profile', $organisation->slug))
        ->assertSee(__('user.profile.one_time_password.title'))
        ->assertDontSee(__('user.profile.pratique_account.heading'))
        ->assertDontSee(__('user.switch_organisation'));
});
