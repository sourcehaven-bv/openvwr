<?php

declare(strict_types=1);

namespace App\Services\Authentication\Pratique\Webhooks;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Applies a verified lifecycle event to local state.
 *
 * THE RULE THIS CLASS EXISTS TO ENFORCE: a missed webhook must never become a
 * security issue. Everything an event reports is already reconciled from the
 * assertion on that user's next request, before any policy is consulted, so
 * authorization is correct whether or not a webhook ever arrives. Events only
 * make something happen *sooner*.
 *
 * Three properties follow, and each is load-bearing:
 *
 *  1. **Narrow only, never widen.** An event may drop a role or end a session.
 *     It may never grant one. Dropping early is safe even if the event is
 *     spurious — the next request restores it from the assertion. Granting early
 *     on a forged or replayed event would be an escalation with nothing to undo
 *     it. So there is no code here that adds anything.
 *  2. **Idempotent and order-independent.** Delivery is at-least-once with
 *     retries, so a duplicate or a stale event has to be a no-op. Every action
 *     below is "delete if present", which is naturally both.
 *  3. **Never trust the payload's contents.** Events carry identifiers and a
 *     `fields` list, not values. Nothing here writes a value taken from the body.
 *
 * An unknown event type is ignored rather than rejected: Pratique emits a wider
 * catalogue than this app cares about, and answering 4xx to an event we simply
 * do not want would make the proxy retry it ~20 times before dead-lettering.
 */
class PratiqueWebhookHandler
{
    public function handle(PratiqueWebhookEvent $event): void
    {
        match ($event->type) {
            PratiqueWebhookEvent::SESSION_REVOKED => $this->revokeSessions($event),
            PratiqueWebhookEvent::MEMBERSHIP_DELETED,
            PratiqueWebhookEvent::MEMBERSHIP_UPDATED => $this->dropOrganisationRoles($event),
            default => null,
        };
    }

    /**
     * End every local session for the user.
     *
     * This is the one thing request-time reconciliation structurally cannot do:
     * a forced logout elsewhere cannot end a session here, because no request
     * arrives to re-check. Purely subtractive — the user simply signs in again
     * through the proxy, which is the correct outcome for a revocation.
     */
    private function revokeSessions(PratiqueWebhookEvent $event): void
    {
        $user = $this->user($event);

        if (!$user instanceof User) {
            return;
        }

        DB::table('sessions')->where('user_id', $user->id->toString())->delete();
    }

    /**
     * Drop the user's roles in the affected organisation.
     *
     * The event says only that a membership changed, never what it changed to,
     * so the roles are removed rather than rewritten. The next request restores
     * whatever the assertion actually grants. That asymmetry is deliberate: this
     * path can only ever reduce access, so a spurious or replayed event costs a
     * user one round-trip, never someone else's privileges.
     *
     * Roles in other organisations are untouched — the event is org-scoped, and
     * so is the effect.
     */
    private function dropOrganisationRoles(PratiqueWebhookEvent $event): void
    {
        $user = $this->user($event);
        $organisationSlug = $event->string('org_slug');

        if (!$user instanceof User) {
            return;
        }

        $query = $user->organisationRoles();

        // Without a resolvable organisation the safe move is to drop the user's
        // roles everywhere: over-revoking costs a round-trip, under-revoking
        // leaves stale privilege visible to anything reading the database.
        if ($organisationSlug !== null) {
            $organisation = $user->organisations()->where('slug', $organisationSlug)->first();

            if ($organisation === null) {
                return;
            }

            $query->where('organisation_id', $organisation->id);
        }

        $query->delete();
    }

    /**
     * The local user an event refers to, matched on the proxy's subject — the
     * same stable identifier the assertion path uses. Absent means nobody here
     * has signed in as them yet, which makes the event a no-op rather than an
     * error: there is no local state to correct.
     */
    private function user(PratiqueWebhookEvent $event): ?User
    {
        $subject = $event->string('user_id');

        if ($subject === null) {
            return null;
        }

        return User::query()->where('pratique_subject', $subject)->first();
    }
}
