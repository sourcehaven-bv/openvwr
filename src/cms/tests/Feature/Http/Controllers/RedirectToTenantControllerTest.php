<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\User;

use function it;

it('redirects when no user is logged in', function (): void {
    $this->get('/')->assertRedirect('login');
});

it('shows 403 when no organisation', function (): void {
    $user = User::factory()
        ->withValidOtpRegistration()
        ->create();

    $this->asFilamentUser($user)
        ->get('/')
        ->assertStatus(403);
});

it('shows 403 when no role in organisation', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasAttached($organisation)
        ->withValidOtpRegistration()
        ->create();

    $this->withFilamentSession($user, $organisation)
        ->get('/')
        ->assertStatus(403);
});

it('redirects to organisation when user has organisation role', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->withValidOtpRegistration()
        ->create();

    $this->withFilamentSession($user, $organisation)
        ->get('/')
        ->assertRedirect($organisation->slug);
});


it('redirects to organisation when user has global role', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()
        ->hasAttached($organisation)
        ->hasGlobalRole(Role::CHIEF_PRIVACY_OFFICER)
        ->withValidOtpRegistration()
        ->create();

    $this->withFilamentSession($user, $organisation)
        ->get('/')
        ->assertRedirect($organisation->slug);
});
