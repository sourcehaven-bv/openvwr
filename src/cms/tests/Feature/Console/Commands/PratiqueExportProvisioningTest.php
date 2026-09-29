<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

/*
 * The export is the input to a script that provisions a real proxy, so what it
 * gets wrong lands in production tenancy. It is read-only by design: these tests
 * pin what it reports, and that it reports nothing else.
 */

/** @return array<int, array<string, mixed>> */
function exportPlan(): array
{
    Artisan::call('pratique:export-provisioning');

    return json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);
}

function exportScript(): string
{
    Artisan::call('pratique:export-provisioning', ['--format' => 'sh']);

    return Artisan::output();
}

/**
 * @param array<int, array<string, mixed>> $plan
 *
 * @return array<string, mixed>|null
 */
function memberIn(array $plan, string $slug, string $email): ?array
{
    foreach ($plan as $organisation) {
        if ($organisation['slug'] !== $slug) {
            continue;
        }

        foreach ($organisation['members'] as $member) {
            if ($member['email'] === $email) {
                return $member;
            }
        }
    }

    return null;
}

it('exports an organisation with its members', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme', 'name' => 'Acme']);
    $user = User::factory()->create(['email' => 'alice@example.org']);
    $user->organisations()->attach($organisation);

    $member = memberIn(exportPlan(), 'acme', 'alice@example.org');

    expect($member)->not->toBeNull()
        ->and($member['roles'])->toBe([]);
});

it('exports the roles a member holds in that organisation', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme']);
    $user = User::factory()->create(['email' => 'alice@example.org']);
    $user->organisations()->attach($organisation);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);

    $member = memberIn(exportPlan(), 'acme', 'alice@example.org');

    expect($member['roles'])->toBe(['privacy-officer']);
});

/*
 * Roles are per-tenant. Exporting a role held in another organisation would
 * hand someone privileges in a tenant where they never had them.
 */
it('does not leak roles from another organisation', function (): void {
    $acme = Organisation::factory()->create(['slug' => 'acme']);
    $other = Organisation::factory()->create(['slug' => 'other']);

    $user = User::factory()->create(['email' => 'alice@example.org']);
    $user->organisations()->attach($acme);
    $user->organisations()->attach($other);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $other);

    expect(memberIn(exportPlan(), 'acme', 'alice@example.org')['roles'])->toBe([]);
});

/*
 * functional-manager is cross-tenant and lives only in this application; the
 * proxy has no equivalent and must not be told about it.
 */
it('does not export global roles', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme']);
    $user = User::factory()->create(['email' => 'alice@example.org']);
    $user->organisations()->attach($organisation);
    $user->assignGlobalRole(Role::FUNCTIONAL_MANAGER);

    expect(memberIn(exportPlan(), 'acme', 'alice@example.org')['roles'])->toBe([]);
});

it('omits a user who belongs to no organisation', function (): void {
    Organisation::factory()->create(['slug' => 'acme']);
    User::factory()->create(['email' => 'nobody@example.org']);

    expect(memberIn(exportPlan(), 'acme', 'nobody@example.org'))->toBeNull();
});

it('produces a runnable script', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme', 'name' => 'Acme']);
    $user = User::factory()->create(['email' => 'alice@example.org']);
    $user->organisations()->attach($organisation);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);

    $script = exportScript();

    expect($script)->toContain("ensure_org 'acme' 'Acme'")
        ->toContain("ensure_member \"\$ORG_ID\" 'alice@example.org' 'privacy-officer'")
        ->toContain('set -euo pipefail');
});

/*
 * The -roles flag defaults to "member", so a role-less member must be passed an
 * explicit empty set rather than having the flag omitted — otherwise the proxy
 * grants a role this application never gave.
 */
it('passes an explicit empty role set for a member with no roles', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme']);
    $user = User::factory()->create(['email' => 'alice@example.org']);
    $user->organisations()->attach($organisation);

    expect(exportScript())->toContain("ensure_member \"\$ORG_ID\" 'alice@example.org' ''");
});

/*
 * Names come from user input, so the script has to survive a quote in one
 * without breaking out of its own argument.
 */
it('escapes a quote in an organisation name', function (): void {
    Organisation::factory()->create(['slug' => 'acme', 'name' => "Bob's Bureau"]);

    expect(exportScript())->toContain("'Bob'\\''s Bureau'");
});

/* It reports; it never provisions. */
it('changes nothing', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme']);
    $user = User::factory()->create(['email' => 'alice@example.org']);
    $user->organisations()->attach($organisation);

    $before = [User::query()->count(), Organisation::query()->count()];
    exportPlan();

    expect([User::query()->count(), Organisation::query()->count()])->toBe($before);
});
