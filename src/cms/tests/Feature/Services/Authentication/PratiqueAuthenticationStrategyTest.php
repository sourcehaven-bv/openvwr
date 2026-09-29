<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Authentication\Pratique\PratiqueAssertionException;
use App\Services\Authentication\Pratique\PratiqueContext;
use App\Services\Authentication\Pratique\PratiqueIdentity;
use App\Services\Authentication\PratiqueAuthenticationStrategy;

function pratiqueStrategyFor(User $user, Organisation $organisation): PratiqueAuthenticationStrategy
{
    $context = new PratiqueContext();
    $context->set(new PratiqueIdentity($user, $organisation));

    return new PratiqueAuthenticationStrategy($context);
}

it('answers with the identity the middleware resolved', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();

    $strategy = pratiqueStrategyFor($user, $organisation);

    expect($strategy->user()->id->toString())->toBe($user->id->toString())
        ->and($strategy->organisation()->id->toString())->toBe($organisation->id->toString());
});

/*
 * Roles come from two places on purpose: those in the active organisation are the
 * proxy's business, while global roles live only here (there is no UI for them in
 * the proxy, and they are cross-tenant by nature).
 */
it('combines global roles with those held in the active organisation', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $user->organisations()->attach($organisation);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);
    $user->assignGlobalRole(Role::FUNCTIONAL_MANAGER);

    $roles = pratiqueStrategyFor($user, $organisation)->principal()->roles;

    expect($roles)->toContain(Role::PRIVACY_OFFICER)
        ->toContain(Role::FUNCTIONAL_MANAGER);
});

it('does not carry roles held in another organisation', function (): void {
    $acme = Organisation::factory()->create();
    $other = Organisation::factory()->create();

    $user = User::factory()->create();
    $user->organisations()->attach($acme);
    $user->organisations()->attach($other);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $other);

    expect(pratiqueStrategyFor($user, $acme)->principal()->roles)->toBe([]);
});

/*
 * Reaching the strategy without the middleware having run is a wiring mistake. It
 * has to read as "not authenticated" rather than quietly answering with nothing.
 */
it('refuses to answer when nothing has been verified', function (): void {
    (new PratiqueAuthenticationStrategy(new PratiqueContext()))->user();
})->throws(PratiqueAssertionException::class);

/* The context reports whether anything has been verified for this request yet. */
it('reports whether an identity has been recorded', function (): void {
    $context = new PratiqueContext();
    expect($context->has())->toBeFalse();

    $context->set(new PratiqueIdentity(User::factory()->create(), Organisation::factory()->create()));
    expect($context->has())->toBeTrue();
});
