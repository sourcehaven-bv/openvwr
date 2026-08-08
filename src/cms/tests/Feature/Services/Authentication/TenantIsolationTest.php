<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Authentication\AuthenticationStrategy;
use Filament\Facades\Filament;

/*
 * The strategy is a container singleton, so anything it caches outlives a single
 * question. Roles are scoped to the ACTIVE organisation, which changes within a
 * request whenever the user switches tenant — so a cached Principal must not
 * survive that switch, or a role held in one org silently applies in another.
 */

it('does not carry roles across a tenant switch', function (): void {
    $orgA = Organisation::factory()->create();
    $orgB = Organisation::factory()->create();

    $user = User::factory()->create();
    $user->organisations()->attach($orgA);
    $user->organisations()->attach($orgB);

    // Privileged in A, no roles at all in B.
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $orgA);

    $this->be($user);

    $strategy = $this->app->get(AuthenticationStrategy::class);

    Filament::setTenant($orgA);
    expect($strategy->principal()->roles)->toContain(Role::PRIVACY_OFFICER);

    Filament::setTenant($orgB);
    expect($strategy->principal()->roles)
        ->toBe([], 'a role held in one organisation leaked into another');
});

it('does not carry roles across a change of user', function (): void {
    $organisation = Organisation::factory()->create();

    $privileged = User::factory()->create();
    $privileged->organisations()->attach($organisation);
    $privileged->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);

    $unprivileged = User::factory()->create();
    $unprivileged->organisations()->attach($organisation);

    $strategy = $this->app->get(AuthenticationStrategy::class);

    $this->be($privileged);
    Filament::setTenant($organisation);
    expect($strategy->principal()->roles)->toContain(Role::PRIVACY_OFFICER);

    $this->be($unprivileged);
    expect($strategy->principal()->roles)
        ->toBe([], 'one user inherited another user\'s roles');
});
