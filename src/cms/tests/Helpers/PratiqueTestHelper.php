<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use OpenSSLAsymmetricKey;
use RuntimeException;

use function base64_encode;
use function bin2hex;
use function gmdate;
use function is_array;
use function openssl_pkey_get_details;
use function openssl_pkey_new;
use function random_bytes;
use function rtrim;
use function strtr;
use function time;

use const OPENSSL_KEYTYPE_EC;

/**
 * Mints assertions the way the proxy would, signed with a real ES256 key.
 *
 * The verifier is this application's trust boundary, so its tests sign and verify
 * for real rather than stubbing the crypto: a mocked signature check would still
 * pass if the production code forgot to check signatures at all.
 */
final class PratiqueTestHelper
{
    public const ISSUER = 'https://auth.test';
    public const AUDIENCE = 'app://openvwr-test';
    public const JWKS_URL = 'https://auth.test/.well-known/pratique/jwks.json';

    /**
     * Default key id prefix. Each helper appends a unique suffix, because two
     * helpers generate DIFFERENT keypairs: sharing one id would let a token
     * signed by helper A be checked against helper B's cached key — and since the
     * ids matched, the verifier would see no reason to refetch. Tests running in
     * parallel then fail with "OpenSSL unable to validate key" depending purely
     * on interleaving.
     */
    public const KEY_ID = 'test-key';

    private OpenSSLAsymmetricKey $privateKey;

    /** @var array<string, mixed> */
    private array $jwk;

    public function __construct(?string $keyId = null)
    {
        $keyId ??= self::KEY_ID . '-' . bin2hex(random_bytes(8));

        $key = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);

        if (!$key instanceof OpenSSLAsymmetricKey) {
            throw new RuntimeException('Could not generate an EC key for the test.');
        }

        $this->privateKey = $key;
        $this->jwk = self::publicJwk($key, $keyId);
    }

    /**
     * The JWKS document the proxy would publish for this key.
     *
     * @return array<string, mixed>
     */
    public function jwks(): array
    {
        return ['keys' => [$this->jwk]];
    }

    /**
     * Publish this key's JWKS to the cache the provider reads, and drop anything a
     * previous test left there.
     *
     * Seeding the cache rather than faking the HTTP call keeps these tests about
     * the verifier: the fetch path has its own tests, and this way a test that
     * cares about a *signature* cannot accidentally pass because the key fetch
     * failed first.
     */
    public function publishJwks(): void
    {
        // MERGE rather than replace. The cache key is shared by every test in the
        // process, so overwriting it would let one test file's key evict
        // another's mid-run — and because the verifier only refetches on an
        // *unknown* kid, the evicted test would fail against a key it never
        // published. Publishing alongside keeps every helper's key valid, which
        // is also what the proxy really does: it serves current + previous.
        $cached = Cache::get('pratique:jwks');
        $keys = is_array($cached) && is_array($cached['keys'] ?? null) ? $cached['keys'] : [];
        $keys[] = $this->jwk;

        Cache::put('pratique:jwks', ['keys' => $keys], 300);
    }

    /** Publish only a key set that does NOT contain the key this helper signs with. */
    public function publishForeignJwks(): void
    {
        Cache::put('pratique:jwks', (new self())->jwks(), 300);
    }

    /**
     * A signed assertion. Every claim can be overridden so a test can express
     * exactly one deviation — a wrong audience, a lapsed expiry — and assert that
     * it alone is rejected.
     *
     * @param array<string, mixed> $overrides
     */
    public function assertion(array $overrides = []): string
    {
        $now = time();

        $claims = [
            'iss' => self::ISSUER,
            'sub' => 'usr_01TESTSUBJECT',
            'email' => 'someone@example.org',
            'email_verified' => true,
            'org_id' => 'org_01TESTORG',
            'org_slug' => 'test-org',
            'roles' => [],
            'aud' => self::AUDIENCE,
            'iat' => $now,
            'nbf' => $now - 5,
            'exp' => $now + 540,
            'principal_type' => 'user',
            ...$overrides,
        ];

        return JWT::encode($claims, $this->privateKey, 'ES256', $this->jwk['kid']);
    }

    /**
     * A signed webhook delivery, shaped as the proxy mints them: the whole body
     * is a JWT carrying id/event/occurred_at/data, stamped with iss/iat/exp and
     * — unlike an assertion — no `aud`.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $overrides
     */
    public function webhook(string $event, array $data = [], array $overrides = []): string
    {
        $now = time();

        $claims = [
            'id' => 'evt_' . $now,
            'event' => $event,
            'occurred_at' => gmdate('c', $now),
            'data' => $data,
            'iss' => self::ISSUER,
            'iat' => $now,
            'exp' => $now + 300,
            ...$overrides,
        ];

        return JWT::encode($claims, $this->privateKey, 'ES256', $this->jwk['kid']);
    }

    /**
     * A webhook signed by a key the proxy does not publish.
     *
     * @param array<string, mixed> $data
     */
    public static function webhookFromForeignKey(string $event, array $data = []): string
    {
        return (new self('attacker-key'))->webhook($event, $data);
    }

    /**
     * An assertion signed by a different key than the one the JWKS publishes.
     *
     * @param array<string, mixed> $overrides
     */
    public static function assertionFromForeignKey(array $overrides = []): string
    {
        return (new self('attacker-key'))->assertion($overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private static function publicJwk(OpenSSLAsymmetricKey $key, string $keyId): array
    {
        $details = openssl_pkey_get_details($key);

        if ($details === false || !isset($details['ec']['x'], $details['ec']['y'])) {
            throw new RuntimeException('Could not read the generated EC key.');
        }

        return [
            'kty' => 'EC',
            'crv' => 'P-256',
            'kid' => $keyId,
            'alg' => 'ES256',
            'use' => 'sig',
            'x' => self::base64Url($details['ec']['x']),
            'y' => self::base64Url($details['ec']['y']),
        ];
    }

    private static function base64Url(string $binary): string
    {
        return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
    }
}
