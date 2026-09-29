<?php

declare(strict_types=1);

namespace App\Services\Authentication\Pratique\Webhooks;

use App\Services\Authentication\Pratique\JwksProvider;
use App\Services\Authentication\Pratique\PratiqueAssertionException;
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
use function trim;

/**
 * Verifies a webhook delivery from the proxy.
 *
 * The whole request body is a signed JWT (`Content-Type: application/jwt`),
 * signed with the same ES256 key as assertions and verifiable against the same
 * published JWKS — so the key handling is shared with the assertion path.
 *
 * It is a SEPARATE verifier all the same, for one concrete reason: a webhook
 * token carries `iss`, `iat` and `exp` but **no `aud`**. The assertion verifier
 * requires a strict audience match, and that requirement is the confused-deputy
 * guard protecting every authenticated request — relaxing it to accommodate
 * webhooks would weaken the far more important path. Two verifiers, each strict
 * about what its own token actually carries.
 *
 * This endpoint is unauthenticated by necessity (the proxy holds no session for
 * it), so the signature IS the authentication. Everything here fails closed.
 */
class PratiqueWebhookVerifier
{
    public function __construct(
        private readonly JwksProvider $jwks,
        private readonly string $issuer,
        private readonly int $leewaySeconds,
    ) {
    }

    /**
     * @throws PratiqueWebhookException when the delivery is not a token this proxy signed
     */
    public function verify(Request $request): PratiqueWebhookEvent
    {
        $token = trim($request->getContent());

        if ($token === '') {
            throw PratiqueWebhookException::missingBody();
        }

        return PratiqueWebhookEvent::fromClaims($this->decode($token));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PratiqueWebhookException
     */
    private function decode(string $token): array
    {
        JWT::$leeway = $this->leewaySeconds;

        try {
            $decoded = JWT::decode($token, $this->keysFor($token));
        } catch (PratiqueAssertionException $exception) {
            // The key fetch failed. Surfaced as a webhook failure so the caller
            // answers 5xx and the proxy retries, rather than 4xx which it would
            // treat as a permanent rejection.
            throw PratiqueWebhookException::notVerified($exception->getMessage());
        } catch (Throwable $exception) {
            throw PratiqueWebhookException::notVerified($exception->getMessage());
        }

        /** @var array<string, mixed> $claims */
        $claims = (array) $decoded;

        $this->assertIssuer($claims);

        return $claims;
    }

    /**
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
     * @throws PratiqueWebhookException
     */
    private function assertIssuer(array $claims): void
    {
        $issuer = $claims['iss'] ?? null;

        if (!is_string($issuer) || !hash_equals($this->issuer, $issuer)) {
            throw PratiqueWebhookException::notVerified('the issuer does not match');
        }
    }
}
