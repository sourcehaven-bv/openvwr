<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Authentication\Pratique\PratiqueAssertion;
use App\Services\Authentication\Pratique\PratiqueAssertionException;
use App\Services\Authentication\Pratique\PratiqueIdentityResolver;

/*
 * The proxy owns authentication; this app still owns its domain data, and
 * snapshot approvals, audit entries and mandate-holder links all point at local
 * user rows. So every request reconciles the assertion against those rows.
 */

/** @param array<string, mixed> $overrides */
function assertionFor(array $overrides = []): PratiqueAssertion
{
    return PratiqueAssertion::fromClaims([
        'sub' => 'usr_alice',
        'email' => 'alice@example.org',
        'email_verified' => true,
        'org_id' => 'org_acme',
        'org_slug' => 'acme',
        'roles' => [],
        'principal_type' => 'user',
        ...$overrides,
    ]);
}

function resolver(): PratiqueIdentityResolver
{
    return app(PratiqueIdentityResolver::class);
}

it('creates a user on first sight and links them to the organisation', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme']);

    $identity = resolver()->resolve(assertionFor());

    expect($identity->user->pratique_subject)->toBe('usr_alice')
        ->and($identity->user->email)->toBe('alice@example.org')
        ->and($identity->organisation->id->toString())->toBe($organisation->id->toString())
        ->and($identity->user->organisations()->count())->toBe(1);
});

it('reuses the same local user on later requests', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    $first = resolver()->resolve(assertionFor());
    $second = resolver()->resolve(assertionFor());

    expect($second->user->id->toString())->toBe($first->user->id->toString())
        ->and(User::query()->count())->toBe(1);
});

/*
 * Email is mutable in the proxy — users can change their own. Matching on it
 * would split one person across two rows after a change of address, orphaning
 * everything the old row owns.
 */
it('follows a changed email address on the same subject', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    $first = resolver()->resolve(assertionFor());
    $second = resolver()->resolve(assertionFor(['email' => 'alice.new@example.org']));

    expect($second->user->id->toString())->toBe($first->user->id->toString())
        ->and($second->user->email)->toBe('alice.new@example.org')
        ->and(User::query()->count())->toBe(1);
});

it('treats a different subject as a different person', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    resolver()->resolve(assertionFor(['sub' => 'usr_alice']));
    resolver()->resolve(assertionFor(['sub' => 'usr_bob', 'email' => 'bob@example.org']));

    expect(User::query()->count())->toBe(2);
});

it('grants the roles the assertion carries', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    $identity = resolver()->resolve(assertionFor(['roles' => ['privacy-officer', 'counselor']]));

    $roles = $identity->user->organisationRoles()->get()->map(fn ($r): string => $r->role->value)->all();

    expect($roles)->toContain('privacy-officer')->toContain('counselor');
});

/*
 * A role withdrawn in the proxy has to disappear here on the next request, or
 * revocation would never reach the app.
 */
it('withdraws a role that the assertion no longer carries', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    resolver()->resolve(assertionFor(['roles' => ['privacy-officer', 'counselor']]));
    $identity = resolver()->resolve(assertionFor(['roles' => ['counselor']]));

    $roles = $identity->user->organisationRoles()->get()->map(fn ($r): string => $r->role->value)->all();

    expect($roles)->toBe(['counselor']);
});

/*
 * Role sync is scoped to the organisation the assertion is about. What the user
 * holds elsewhere is none of this assertion's business.
 */
it('leaves roles in other organisations alone', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);
    $other = Organisation::factory()->create(['slug' => 'other']);

    $identity = resolver()->resolve(assertionFor(['roles' => ['privacy-officer']]));
    $identity->user->assignOrganisationRole(Role::COUNSELOR, $other);

    resolver()->resolve(assertionFor(['roles' => []]));

    $remaining = $identity->user->organisationRoles()
        ->where('organisation_id', $other->id)
        ->get()
        ->map(fn ($r): string => $r->role->value)
        ->all();

    expect($remaining)->toBe(['counselor']);
});

/*
 * The proxy's role catalogue is edited independently of this app. A role added
 * there should not lock out a whole tenant until this app is redeployed.
 */
it('ignores a role this application does not define', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    $identity = resolver()->resolve(assertionFor(['roles' => ['privacy-officer', 'some-future-role']]));

    $roles = $identity->user->organisationRoles()->get()->map(fn ($r): string => $r->role->value)->all();

    expect($roles)->toBe(['privacy-officer']);
});

/*
 * Organisations are never conjured from a claim: a tenant here owns registers,
 * numbering sequences and a published website, so a typo'd slug in the proxy must
 * not quietly produce an empty parallel tenant.
 */
it('refuses an organisation this application does not know', function (): void {
    resolver()->resolve(assertionFor(['org_slug' => 'does-not-exist']));
})->throws(PratiqueAssertionException::class);

it('does not touch global roles', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);

    $identity = resolver()->resolve(assertionFor());
    $identity->user->assignGlobalRole(Role::FUNCTIONAL_MANAGER);

    $again = resolver()->resolve(assertionFor(['roles' => ['counselor']]));

    expect($again->user->globalRoles()->count())->toBe(1);
});
