<?php

declare(strict_types=1);

namespace App\Services\Authentication\Pratique;

use function is_array;
use function is_bool;
use function is_string;

/**
 * The verified claims of a Pratique assertion.
 *
 * Only ever constructed from an assertion whose signature, issuer, audience and
 * validity window have already been checked (see PratiqueAssertionVerifier). It
 * exists so the rest of the app handles a typed value rather than a loose array,
 * and so "has this been verified?" is answered by the type.
 */
final readonly class PratiqueAssertion
{
    /**
     * @param array<int, string> $roles roles held in the active organisation only
     */
    public function __construct(
        public string $subject,
        public string $email,
        public bool $emailVerified,
        public string $organisationId,
        public string $organisationSlug,
        public array $roles,
        public string $principalType,
    ) {
    }

    /**
     * @param array<string, mixed> $claims a payload that has already been verified
     *
     * @throws PratiqueAssertionException when a required claim is absent or the wrong type
     */
    public static function fromClaims(array $claims): self
    {
        return new self(
            subject: self::requireString($claims, 'sub'),
            email: self::requireString($claims, 'email'),
            emailVerified: self::optionalBool($claims, 'email_verified'),
            organisationId: self::requireString($claims, 'org_id'),
            organisationSlug: self::requireString($claims, 'org_slug'),
            roles: self::optionalStringList($claims, 'roles'),
            // principal_type is always set by the proxy, but default rather than
            // reject: an older proxy omitting it should not lock everyone out, and
            // "user" is the only kind this app understands anyway.
            principalType: self::optionalString($claims, 'principal_type', 'user'),
        );
    }

    /**
     * @param array<string, mixed> $claims
     *
     * @throws PratiqueAssertionException
     */
    private static function requireString(array $claims, string $key): string
    {
        $value = $claims[$key] ?? null;

        if (!is_string($value) || $value === '') {
            throw PratiqueAssertionException::missingClaim($key);
        }

        return $value;
    }

    /** @param array<string, mixed> $claims */
    private static function optionalString(array $claims, string $key, string $default): string
    {
        $value = $claims[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : $default;
    }

    /** @param array<string, mixed> $claims */
    private static function optionalBool(array $claims, string $key): bool
    {
        $value = $claims[$key] ?? null;

        return is_bool($value) ? $value : false;
    }

    /**
     * Roles are absent for a user who is a member of an organisation but holds no
     * role there (an SSO just-in-time signup, for instance). That is a valid
     * state: they authenticate, and every policy denies.
     *
     * @param array<string, mixed> $claims
     *
     * @return array<int, string>
     */
    private static function optionalStringList(array $claims, string $key): array
    {
        $value = $claims[$key] ?? null;

        if (!is_array($value)) {
            return [];
        }

        $strings = [];
        foreach ($value as $item) {
            if (is_string($item) && $item !== '') {
                $strings[] = $item;
            }
        }

        return $strings;
    }
}
