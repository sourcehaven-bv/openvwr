<?php

declare(strict_types=1);

use App\Enums\RouteName;
use App\Models\Organisation;
use Illuminate\Support\Facades\Auth;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\SessionTestHelper;

it('redirects to login', function (): void {
    $this->asFilamentUser();
    Auth::logout();

    $this->get('/')
        ->assertRedirectToRoute(RouteName::FILAMENT_ADMIN_AUTH_LOGIN);
});

it('loads the main page', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->followingRedirects()
        ->get('/')
        ->assertSee($organisation->name);
});

it('gets redirected to the users organisation tenant scoped main page', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get('/')
        ->assertRedirect(sprintf('/%s', $organisation->slug));
});

it('is not allowed to access an unauthorized organisation', function (): void {
    $unauthorizedOrganisation = Organisation::factory()->create();

    $this->asFilamentUser()
        ->get(sprintf('/%s', $unauthorizedOrganisation->slug))
        ->assertNotFound();
});

it('shows 404 when trying to load a url for a non-existing organisation without valid otp session', function (): void {
    $this->asFilamentUser();

    SessionTestHelper::setOtpInvalid();

    $this->get(sprintf('/%s/two-factor-authentication', fake()->slug()))
        ->assertNotFound();
});
