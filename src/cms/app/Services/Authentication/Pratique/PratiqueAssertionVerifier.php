<?php

declare(strict_types=1);

namespace App\Services\Authentication\Pratique;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Throwable;

use function count;
use function explode;
use function hash_equals;
use function is_array;
use function is_string;
use function json_decode;
use function preg_match;

/**
 * Verifies the signed assertion the proxy forwards, and nothing else.
 *
 * This is the app's entire trust boundary under the "pratique" driver: the proxy
 * strips any inbound Authorization header and sets its own, so a validly signed
 * assertion is the only thing that distinguishes an authenticated request from an
 * arbitrary one. Everything here fails closed.
 *
 * Checks, in order:
 *   1. an Authorization: Bearer header is present
 *   2. the signature verifies under a published key (ES256 only)
 *   3. iss equals the configured issuer
 *   4. aud equals the configured audience, exactly
 *   5. exp is in the future and nbf in the past, within a small leeway
 *   6. the required claims are present and well-typed
 *
 * Step 4 is the confused-deputy guard: without a strict audience check an
 * assertion minted for a different upstream would be accepted as one for us.
 */
class PratiqueAssertionVerifier
{
    public function __construct(
        private readonly JwksProvider $jwks,
        private readonly string $issuer,
        private readonly string $audience,
        private readonly int $leewaySeconds,
    ) {
    }

    /**
     * @throws PratiqueAssertionException when the request carries no verifiable assertion
     */
    public function verify(Request $request): PratiqueAssertion
    {
        $token = $this->bearerToken($request);
        $claims = $this->decode($token);

        $assertion = PratiqueAssertion::fromClaims($claims);

        // A machine principal (an OAuth app, a service account, a personal access
        // token) can hold a valid assertion for this audience, but this app's
        // authorisation model is written entirely around people. Refuse rather
        // than half-support them.
        if ($assertion->principalType !== 'user') {
            throw PratiqueAssertionException::unexpectedPrincipal($assertion->principalType);
        }

        return $assertion;
    }

    /**
     * @throws PratiqueAssertionException
     */
    private function bearerToken(Request $request): string
    {
        $header = $request->header('Authorization');

        if (!is_string($header) || $header === '') {
            throw PratiqueAssertionException::missingHeader();
        }

        // Case-insensitive scheme: RFC 7235 makes it so, and the proxy's own
        // verifier accepts either spelling.
        if (preg_match('/^\s*bearer\s+(\S+)\s*$/i', $header, $matches) !== 1) {
            throw PratiqueAssertionException::missingHeader();
        }

        return $matches[1];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PratiqueAssertionException
     */
    private function decode(string $token): array
    {
        JWT::$leeway = $this->leewaySeconds;

        $keys = $this->keysFor($token);

        try {
            // decode() enforces the signature, alg, exp and nbf. It does NOT check
            // iss or aud, so those are asserted explicitly below — forgetting them
            // is the classic way to build a verifier that accepts anyone's token.
            $decoded = JWT::decode($token, $keys);
        } catch (Throwable $exception) {
            throw PratiqueAssertionException::notVerified($exception->getMessage());
        }

        /** @var array<string, mixed> $claims */
        $claims = (array) $decoded;

        $this->assertIssuer($claims);
        $this->assertAudience($claims);

        return $claims;
    }

    /**
     * Resolve the keys to verify with, refetching once if the token names a key we
     * do not know — that is the ordinary case immediately after a key rotation.
     *
     * @return array<string, Key>
     *
     * @throws PratiqueAssertionException
     */
    private function keysFor(string $token): array
    {
        $keys = $this->jwks->keys();
        $kid = $this->keyId($token);

        if ($kid !== null && !isset($keys[$kid])) {
            return $this->jwks->freshKeys();
        }

        return $keys;
    }

    /**
     * The unverified "kid" from the header. Reading an unverified header is safe
     * here because it is only used to *select* a candidate key — the signature
     * check still has to pass against it.
     */
    private function keyId(string $token): ?string
    {
        $segments = explode('.', $token);

        if (count($segments) !== 3) {
            return null;
        }

        $header = json_decode(JWT::urlsafeB64Decode($segments[0]), true);

        if (!is_array($header)) {
            return null;
        }

        $kid = $header['kid'] ?? null;

        return is_string($kid) && $kid !== '' ? $kid : null;
    }

    /**
     * @param array<string, mixed> $claims
     *
     * @throws PratiqueAssertionException
     */
    private function assertIssuer(array $claims): void
    {
        $issuer = $claims['iss'] ?? null;

        if (!is_string($issuer) || !hash_equals($this->issuer, $issuer)) {
            throw PratiqueAssertionException::notVerified('the issuer does not match');
        }
    }

    /**
     * Strict, single-valued audience. The proxy mints one audience per upstream,
     * so accepting a list — or a prefix, or a case-insensitive match — would let an
     * assertion intended for a different app through.
     *
     * @param array<string, mixed> $claims
     *
     * @throws PratiqueAssertionException
     */
    private function assertAudience(array $claims): void
    {
        $audience = $claims['aud'] ?? null;

        if (!is_string($audience) || !hash_equals($this->audience, $audience)) {
            throw PratiqueAssertionException::notVerified('the audience does not match');
        }
    }
}
