<?php

declare(strict_types=1);

namespace App\Services\Authentication\Pratique;

use RuntimeException;

use function sprintf;

/**
 * A request could not be authenticated from its assertion.
 *
 * The reason is recorded for the application log but is deliberately never shown
 * to the caller: "which check did I fail?" is an oracle for someone probing the
 * boundary. The middleware turns any of these into a bare 403.
 */
class PratiqueAssertionException extends RuntimeException
{
    public static function missingHeader(): self
    {
        return new self('No Authorization: Bearer assertion was present on the request.');
    }

    public static function missingClaim(string $claim): self
    {
        return new self(sprintf('The assertion is missing the required "%s" claim.', $claim));
    }

    public static function notVerified(string $reason): self
    {
        return new self(sprintf('The assertion could not be verified: %s', $reason));
    }

    public static function misconfigured(string $setting): self
    {
        return new self(sprintf('The "pratique" auth driver requires %s to be configured.', $setting));
    }

    public static function jwksUnavailable(string $reason): self
    {
        return new self(sprintf('The signing keys could not be fetched: %s', $reason));
    }

    public static function unexpectedPrincipal(string $principalType): self
    {
        return new self(sprintf('This application only accepts user principals; got "%s".', $principalType));
    }

    public static function tenantMismatch(string $routeSlug, string $assertionSlug): self
    {
        return new self(sprintf('The requested organisation "%s" is not the one the assertion grants ("%s").', $routeSlug, $assertionSlug));
    }
}
