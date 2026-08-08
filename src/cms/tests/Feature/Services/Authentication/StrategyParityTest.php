<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\Principal;
use App\Models\User;
use App\Services\Authentication\AuthenticationStrategy;
use App\Services\Authentication\BuiltinAuthenticationStrategy;
use App\Services\Authentication\DevAuthenticationStrategy;
use App\Services\AuthenticationService;
use Filament\Facades\Filament;

/*
 * The strategy seam is only worth anything if two implementations genuinely
 * answer the same questions the same way. These tests run the same fixture
 * through each strategy and assert the answers match — that is what stops the
 * interface from being indirection that happens to compile.
 *
 * When the Pratique strategy lands it becomes a third case here rather than new
 * scaffolding.
 */

/** @return array<string, AuthenticationStrategy> */
function strategies(): array
{
    return [
        'builtin' => new BuiltinAuthenticationStrategy(),
        'dev' => new DevAuthenticationStrategy(),
    ];
}

it('resolves the same user under every strategy', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $user->organisations()->attach($organisation);

    $this->be($user);
    Filament::setTenant($organisation);

    foreach (strategies() as $name => $strategy) {
        expect($strategy->user()->id->toString())
            ->toBe($user->id->toString(), sprintf('strategy "%s" resolved a different user', $name));
    }
});

it('resolves the same organisation under every strategy', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $user->organisations()->attach($organisation);

    $this->be($user);
    Filament::setTenant($organisation);

    foreach (strategies() as $name => $strategy) {
        expect($strategy->organisation()->id->toString())
            ->toBe($organisation->id->toString(), sprintf('strategy "%s" resolved a different organisation', $name));
    }
});

it('resolves the same roles under every strategy', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $user->organisations()->attach($organisation);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);
    $user->assignGlobalRole(Role::FUNCTIONAL_MANAGER);

    $this->be($user);
    Filament::setTenant($organisation);

    $roleSets = [];
    foreach (strategies() as $name => $strategy) {
        $principal = $strategy->principal();
        expect($principal)->toBeInstanceOf(Principal::class);

        $roles = array_map(static fn (Role $role): string => $role->value, $principal->roles);
        sort($roles);
        $roleSets[$name] = $roles;
    }

    // Global and organisation roles both present, and identical across strategies.
    expect($roleSets['builtin'])
        ->toContain(Role::PRIVACY_OFFICER->value)
        ->toContain(Role::FUNCTIONAL_MANAGER->value)
        ->and($roleSets['dev'])->toBe($roleSets['builtin']);
});

/*
 * With no authenticated user, role lookup must degrade to "no roles" rather than
 * blowing up — every policy then denies, which is the safe direction. Both role
 * sources (global and per-organisation) have to fail that way independently.
 */
it('yields no roles when there is no authenticated user', function (): void {
    foreach (strategies() as $name => $strategy) {
        expect($strategy->principal()->roles)
            ->toBe([], sprintf('strategy "%s" leaked roles without an authenticated user', $name));
    }
});

it('yields no organisation roles when authenticated without a tenant', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $user->organisations()->attach($organisation);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);
    $user->assignGlobalRole(Role::FUNCTIONAL_MANAGER);

    $this->be($user);
    Filament::setTenant(null);

    // Global roles still resolve; organisation roles cannot, because there is no
    // active tenant to scope them to.
    foreach (strategies() as $strategy) {
        $roles = array_map(static fn (Role $role): string => $role->value, $strategy->principal()->roles);

        expect($roles)
            ->toContain(Role::FUNCTIONAL_MANAGER->value)
            ->not->toContain(Role::PRIVACY_OFFICER->value);
    }
});

/*
 * The facade is the contract ~50 files depend on. It must keep answering through
 * whichever strategy is bound, without those callers knowing which one.
 */
it('serves the facade through the bound strategy', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $user->organisations()->attach($organisation);

    $this->be($user);
    Filament::setTenant($organisation);

    $this->app->instance(AuthenticationStrategy::class, new DevAuthenticationStrategy());

    $service = new AuthenticationService($this->app->get(AuthenticationStrategy::class));

    expect($service->user()->id->toString())->toBe($user->id->toString())
        ->and($service->organisation()->id->toString())->toBe($organisation->id->toString());
});
