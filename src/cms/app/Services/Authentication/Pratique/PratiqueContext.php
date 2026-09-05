<?php

declare(strict_types=1);

namespace App\Services\Authentication\Pratique;

/**
 * Holds the identity resolved for the current request.
 *
 * The middleware verifies once and stores the result here; the strategy reads it.
 * Bound per-request (scoped, not singleton) so a resolved identity can never
 * outlive the request it belongs to — an unkeyed cache on a singleton is exactly
 * how the builtin strategy leaked roles across tenants once before.
 */
class PratiqueContext
{
    private ?PratiqueIdentity $identity = null;

    public function set(PratiqueIdentity $identity): void
    {
        $this->identity = $identity;
    }

    /**
     * @throws PratiqueAssertionException when nothing has been verified for this request
     */
    public function get(): PratiqueIdentity
    {
        if ($this->identity === null) {
            // Reaching here means a route resolved identity without the verifying
            // middleware having run. That is a wiring mistake, and it must read as
            // "not authenticated" rather than quietly returning nothing.
            throw PratiqueAssertionException::missingHeader();
        }

        return $this->identity;
    }

    public function has(): bool
    {
        return $this->identity !== null;
    }
}
