<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Enums\Authorization\Role;
use App\Http\Controllers\RedirectToTenantController;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Authentication\AuthenticationStrategy;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Helpers\NoLoginPageAuthenticationStrategy;

use function afterEach;
use function app;
use function expect;
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

/*
 * Under the builtin driver the panel's gate stops an unauthenticated visitor
 * before the controller runs, so these two branches are reached by invoking it
 * directly. They are what keeps "not signed in" from becoming a 500: with no
 * login page there is no route to build, so the only honest answer is a refusal.
 */

afterEach(function (): void {
    // The binding outlives the test, so put it back.
    app()->forgetInstance(AuthenticationStrategy::class);
});

it('refuses when there is no user and the strategy has no login page', function (): void {
    app()->forgetInstance(AuthenticationStrategy::class);
    app()->instance(AuthenticationStrategy::class, new NoLoginPageAuthenticationStrategy());

    expect(static fn (): RedirectResponse => app(RedirectToTenantController::class)())
        ->toThrow(fn (HttpException $e) => expect($e->getStatusCode())->toBe(403));
});

it('sends an unauthenticated visitor to the login page when there is one', function (): void {
    expect(app(RedirectToTenantController::class)()->getTargetUrl())
        ->toContain('login');
});
