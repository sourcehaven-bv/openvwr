<?php

declare(strict_types=1);

namespace App\Services\Authentication\Pratique;

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function array_map;
use function in_array;

/**
 * Turns a verified assertion into the local rows the application works with.
 *
 * The proxy owns *authentication*; this app still owns its domain data, and
 * snapshot approvals, audit entries and mandate-holder links all point at local
 * user rows. So each request reconciles: find (or create) the user the assertion
 * names, make sure they are a member of the asserted organisation, and bring
 * their roles in that organisation into line with the claim.
 *
 * Roles in the claim are authoritative for the active organisation. Global roles
 * are deliberately not touched — they live only in this app (see the migration
 * plan, §3.2).
 */
class PratiqueIdentityResolver
{
    /**
     * @throws PratiqueAssertionException when the asserted organisation is unknown here
     */
    public function resolve(PratiqueAssertion $assertion): PratiqueIdentity
    {
        return DB::transaction(function () use ($assertion): PratiqueIdentity {
            $organisation = $this->organisation($assertion);
            $user = $this->user($assertion);

            $this->syncMembership($user, $organisation);
            $this->syncRoles($user, $organisation, $assertion);

            return new PratiqueIdentity($user, $organisation);
        });
    }

    /**
     * Organisations are NOT created on the fly. A tenant here owns registers,
     * numbering sequences and published websites; conjuring one from a claim would
     * make a typo'd slug in the proxy silently produce an empty parallel tenant.
     * Provisioning stays deliberate.
     *
     * @throws PratiqueAssertionException
     */
    private function organisation(PratiqueAssertion $assertion): Organisation
    {
        $organisation = Organisation::query()
            ->where('slug', $assertion->organisationSlug)
            ->first();

        if (!$organisation instanceof Organisation) {
            throw PratiqueAssertionException::notVerified('the asserted organisation is not known to this application');
        }

        return $organisation;
    }

    /**
     * Matched on the proxy's subject, never on email — email is mutable there, so
     * matching on it would split one person across rows after a change of address.
     * The name and email are refreshed on every request so the local copy cannot
     * drift from the identity provider.
     */
    private function user(PratiqueAssertion $assertion): User
    {
        $user = User::query()
            ->where('pratique_subject', $assertion->subject)
            ->first();

        if (!$user instanceof User) {
            $user = new User();
            $user->pratique_subject = $assertion->subject;
        }

        $user->email = $assertion->email;

        // The proxy has no display name of its own, so seed one on first sight and
        // leave it alone afterwards: this app lets users edit their own name, and
        // overwriting it every request would undo that.
        if (($user->name ?? '') === '') {
            $user->name = $assertion->email;
        }

        $user->save();

        return $user;
    }

    private function syncMembership(User $user, Organisation $organisation): void
    {
        $user->organisations()->syncWithoutDetaching([$organisation->id->toString()]);
    }

    /**
     * Bring the user's roles in this organisation into line with the claim.
     *
     * Scoped strictly to the asserted organisation: a role removed in the proxy
     * must disappear here on the next request, but memberships and roles in *other*
     * organisations are none of this assertion's business.
     */
    private function syncRoles(User $user, Organisation $organisation, PratiqueAssertion $assertion): void
    {
        $asserted = $this->assertedRoles($assertion);

        $existing = $user->organisationRoles()
            ->where('organisation_id', $organisation->id)
            ->get();

        foreach ($existing as $organisationUserRole) {
            if (!in_array($organisationUserRole->role, $asserted, true)) {
                $organisationUserRole->delete();
            }
        }

        $held = array_map(
            static fn ($organisationUserRole): Role => $organisationUserRole->role,
            $existing->all(),
        );

        foreach ($asserted as $role) {
            if (!in_array($role, $held, true)) {
                $user->assignOrganisationRole($role, $organisation);
            }
        }
    }

    /**
     * Roles the claim names, restricted to those this application defines.
     *
     * An unrecognised role string is ignored rather than fatal: the proxy's role
     * catalogue is edited independently of this app, and a newly added role there
     * should not lock out every user of a tenant until this app is redeployed.
     *
     * @return array<int, Role>
     */
    private function assertedRoles(PratiqueAssertion $assertion): array
    {
        $roles = [];

        foreach ($assertion->roles as $role) {
            $case = Role::tryFrom($role);

            if ($case !== null) {
                $roles[] = $case;
            }
        }

        return $roles;
    }
}
