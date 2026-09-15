<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookEvent;
use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookHandler;
use Illuminate\Support\Facades\DB;

/*
 * The rule this handler exists to enforce: a missed webhook must never become a
 * security issue. Everything an event reports is reconciled from the assertion on
 * the user's next request anyway, so events only make things happen sooner.
 *
 * Three properties follow, and each has tests below: narrow only (never widen),
 * idempotent, and never trust the payload's contents.
 */

function handler(): PratiqueWebhookHandler
{
    return app(PratiqueWebhookHandler::class);
}

/** @param array<string, mixed> $data */
function webhookEvent(string $type, array $data): PratiqueWebhookEvent
{
    return new PratiqueWebhookEvent('evt_1', $type, $data);
}

function userWithRole(Organisation $organisation, Role $role, string $subject = 'usr_alice'): User
{
    $user = User::factory()->create(['pratique_subject' => $subject]);
    $user->organisations()->attach($organisation);
    $user->assignOrganisationRole($role, $organisation);

    return $user;
}

it('drops the roles a membership event reports', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme']);
    $user = userWithRole($organisation, Role::PRIVACY_OFFICER);

    handler()->handle(webhookEvent(PratiqueWebhookEvent::MEMBERSHIP_DELETED, [
        'user_id' => 'usr_alice',
        'org_slug' => 'acme',
    ]));

    expect($user->organisationRoles()->count())->toBe(0);
});

/*
 * NARROW ONLY. There is deliberately no path here that grants anything: dropping
 * a role early is harmless because the next request restores it from the
 * assertion, but granting one on a forged or replayed event would be an
 * escalation with nothing to undo it.
 */
it('never grants a role, whatever the event says', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme']);
    $user = User::factory()->create(['pratique_subject' => 'usr_alice']);
    $user->organisations()->attach($organisation);

    handler()->handle(webhookEvent(PratiqueWebhookEvent::MEMBERSHIP_UPDATED, [
        'user_id' => 'usr_alice',
        'org_slug' => 'acme',
        // A forged event naming a role it would like the user to hold.
        'roles' => ['functional-manager'],
    ]));

    expect($user->organisationRoles()->count())->toBe(0);
});

/* Scoped to the organisation the event is about; other tenants are untouched. */
it('leaves roles in other organisations alone', function (): void {
    $acme = Organisation::factory()->create(['slug' => 'acme']);
    $other = Organisation::factory()->create(['slug' => 'other']);

    $user = userWithRole($acme, Role::PRIVACY_OFFICER);
    $user->organisations()->attach($other);
    $user->assignOrganisationRole(Role::COUNSELOR, $other);

    handler()->handle(webhookEvent(PratiqueWebhookEvent::MEMBERSHIP_DELETED, [
        'user_id' => 'usr_alice',
        'org_slug' => 'acme',
    ]));

    $remaining = $user->organisationRoles()->get()->map(fn ($r): string => $r->role->value)->all();

    expect($remaining)->toBe(['counselor']);
});

/*
 * IDEMPOTENT. Delivery is at-least-once with retries, so a duplicate has to be a
 * no-op rather than an error or a second effect.
 */
it('is a no-op when the same event arrives twice', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme']);
    $user = userWithRole($organisation, Role::PRIVACY_OFFICER);

    $duplicate = webhookEvent(PratiqueWebhookEvent::MEMBERSHIP_DELETED, [
        'user_id' => 'usr_alice',
        'org_slug' => 'acme',
    ]);

    handler()->handle($duplicate);
    handler()->handle($duplicate);

    expect($user->organisationRoles()->count())->toBe(0);
});

it('ends the local sessions of a revoked user', function (): void {
    $user = User::factory()->create(['pratique_subject' => 'usr_alice']);
    $other = User::factory()->create(['pratique_subject' => 'usr_bob']);

    foreach ([$user, $other] as $index => $each) {
        DB::table('sessions')->insert([
            'id' => 'session-' . $index,
            'user_id' => $each->id->toString(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => '',
            'last_activity' => time(),
        ]);
    }

    handler()->handle(webhookEvent(PratiqueWebhookEvent::SESSION_REVOKED, ['user_id' => 'usr_alice']));

    expect(DB::table('sessions')->where('user_id', $user->id->toString())->count())->toBe(0)
        ->and(DB::table('sessions')->where('user_id', $other->id->toString())->count())->toBe(1);
});

/* An event about someone who has never signed in here has no local state to correct. */
it('ignores an event for an unknown user', function (): void {
    handler()->handle(webhookEvent(PratiqueWebhookEvent::SESSION_REVOKED, ['user_id' => 'usr_nobody']));
})->throwsNoExceptions();

/*
 * Pratique emits a wider catalogue than this app cares about. Answering with an
 * error to an event we simply do not want would make the proxy retry it ~20
 * times before dead-lettering it.
 */
it('ignores an event type it does not act on', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme']);
    $user = userWithRole($organisation, Role::PRIVACY_OFFICER);

    handler()->handle(webhookEvent('organization.updated', ['org_slug' => 'acme']));

    expect($user->organisationRoles()->count())->toBe(1);
});

/*
 * With no resolvable organisation the safe move is to over-revoke: costing a
 * user one round-trip beats leaving stale privilege in the database.
 */
it('drops roles everywhere when the event names no organisation', function (): void {
    $acme = Organisation::factory()->create(['slug' => 'acme']);
    $user = userWithRole($acme, Role::PRIVACY_OFFICER);

    handler()->handle(webhookEvent(PratiqueWebhookEvent::MEMBERSHIP_DELETED, ['user_id' => 'usr_alice']));

    expect($user->organisationRoles()->count())->toBe(0);
});

/* A revocation naming nobody has nothing to act on. */
it('ignores a session revocation with no user', function (): void {
    handler()->handle(webhookEvent(PratiqueWebhookEvent::SESSION_REVOKED, []));
})->throwsNoExceptions();

/* Likewise a membership event that names no user. */
it('ignores a membership event with no user', function (): void {
    handler()->handle(webhookEvent(PratiqueWebhookEvent::MEMBERSHIP_DELETED, ['org_slug' => 'acme']));
})->throwsNoExceptions();

/*
 * An organisation the user is not a member of cannot be the subject of their
 * membership change; doing nothing is safer than guessing which org was meant.
 */
it('ignores an organisation the user does not belong to', function (): void {
    $acme = Organisation::factory()->create(['slug' => 'acme']);
    Organisation::factory()->create(['slug' => 'elsewhere']);
    $user = userWithRole($acme, Role::PRIVACY_OFFICER);

    handler()->handle(webhookEvent(PratiqueWebhookEvent::MEMBERSHIP_DELETED, [
        'user_id' => 'usr_alice',
        'org_slug' => 'elsewhere',
    ]));

    expect($user->organisationRoles()->count())->toBe(1);
});
