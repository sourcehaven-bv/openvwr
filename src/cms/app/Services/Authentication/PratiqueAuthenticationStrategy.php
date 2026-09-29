<?php

declare(strict_types=1);

namespace App\Services\Authentication;

use App\Enums\Authorization\Role;
use App\Models\Organisation;
use App\Models\Principal;
use App\Models\User;
use App\Services\Authentication\Pratique\PratiqueContext;

use function array_map;

/**
 * Identity from the Pratique proxy's signed assertion.
 *
 * The proxy authenticates the user and forwards a short-lived assertion; the
 * middleware verifies it and resolves it to local rows. This strategy reads that
 * result — it never inspects the request itself, so there is exactly one place
 * where an assertion is trusted.
 *
 * Roles come from two places on purpose: those held in the active organisation
 * arrive in the claim (the proxy owns tenant membership), while global roles stay
 * local to this app (see the migration plan, §3.2 — there is no UI for them and
 * they are cross-tenant by nature).
 */
class PratiqueAuthenticationStrategy implements AuthenticationStrategy
{
    public function __construct(
        private readonly PratiqueContext $context,
    ) {
    }

    public function user(): User
    {
        return $this->context->get()->user;
    }

    public function organisation(): Organisation
    {
        return $this->context->get()->organisation;
    }

    public function principal(): Principal
    {
        $identity = $this->context->get();

        $globalRoles = array_map(
            static fn ($globalRole): Role => $globalRole->role,
            $identity->user->globalRoles->all(),
        );

        // Organisation roles are read back from the local rows the resolver just
        // reconciled rather than from the claim directly, so that both drivers
        // answer this question from the same source.
        $organisationRoles = array_map(
            static fn ($organisationRole): Role => $organisationRole->role,
            $identity->user
                ->organisationRoles()
                ->where('organisation_id', $identity->organisation->id)
                ->get()
                ->all(),
        );

        return new Principal([...$globalRoles, ...$organisationRoles]);
    }
}
