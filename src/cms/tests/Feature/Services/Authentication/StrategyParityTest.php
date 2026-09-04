<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\Principal;
use App\Models\User;
use App\Services\Authentication\AuthenticationStrategy;
use App\Services\Authentication\BuiltinAuthenticationStrategy;
use App\Services\Authentication\DevAuthenticationStrategy;
use App\Services\Authentication\Pratique\PratiqueContext;
use App\Services\Authentication\Pratique\PratiqueIdentity;
use App\Services\Authentication\PratiqueAuthenticationStrategy;
use App\Services\AuthenticationService;
use Filament\Facades\Filament;

/*
 * The strategy seam is only worth anything if two implementations genuinely
 * answer the same questions the same way. These tests run the same fixture
 * through each strategy and assert the answers match — that is what stops the
 * interface from being indirection that happens to compile.
 *
 * The pratique strategy is the genuinely independent one: it reads a verified
 * assertion rather than the session guard, so agreement between it and the other
 * two is real evidence that the seam holds rather than a language guarantee.
 */

/**
 * Every strategy, set up to answer about the same user in the same organisation.
 *
 * builtin and dev read the session guard and the Filament tenant, which the
 * caller is expected to have set; pratique is handed the identity the middleware
 * would have resolved.
 *
 * @return array<string, AuthenticationStrategy>
 */
function strategies(?User $user = null, ?Organisation $organisation = null): array
{
    $strategies = [
        'builtin' => new BuiltinAuthenticationStrategy(),
        'dev' => new DevAuthenticationStrategy(),
    ];

    if ($user instanceof User && $organisation instanceof Organisation) {
        $context = new PratiqueContext();
        $context->set(new PratiqueIdentity($user, $organisation));
        $strategies['pratique'] = new PratiqueAuthenticationStrategy($context);
    }

    return $strategies;
}

it('resolves the same user under every strategy', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $user->organisations()->attach($organisation);

    $this->be($user);
    Filament::setTenant($organisation);

    foreach (strategies($user, $organisation) as $name => $strategy) {
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

    foreach (strategies($user, $organisation) as $name => $strategy) {
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
    foreach (strategies($user, $organisation) as $name => $strategy) {
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
        ->and($roleSets['dev'])->toBe($roleSets['builtin'])
        ->and($roleSets['pratique'])->toBe($roleSets['builtin']);
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
