<?php

declare(strict_types=1);

namespace App\Services\Authentication\Pratique;

use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\Factory as Http;
use Throwable;

use function is_array;

/**
 * Supplies the proxy's public signing keys, cached.
 *
 * Pratique rotates its signing key on a schedule and publishes the current and
 * previous key together, so a verifier that pins one key will start failing the
 * moment a rotation happens. This fetches the whole set, caches it briefly, and
 * refetches when asked for a key id it has not seen — which is what makes a
 * rotation a non-event rather than an outage.
 */
class JwksProvider
{
    private const CACHE_KEY = 'pratique:jwks';

    /** Set while a refetch is cooling down. */
    private const REFETCH_KEY = 'pratique:jwks:refetched';

    /**
     * How long after a refetch the next one is refused. Long enough that a
     * stream of tokens with made-up key ids costs one fetch per window rather
     * than one per request; short enough that a real rotation in that window
     * delays logins by seconds, not minutes.
     */
    private const REFETCH_COOLDOWN_SECONDS = 10;

    public function __construct(
        private readonly Http $http,
        private readonly Cache $cache,
        private readonly string $jwksUrl,
        private readonly int $cacheSeconds,
    ) {
    }

    /**
     * All currently published keys, indexed by key id.
     *
     * @return array<string, Key>
     *
     * @throws PratiqueAssertionException
     */
    public function keys(): array
    {
        return $this->parse($this->cachedDocument());
    }

    /**
     * The keys, guaranteed to have been fetched fresh rather than read from cache.
     * Used when a token names a key id we do not know: either it is a rotation we
     * have not picked up yet, or the token is bogus — and one refetch tells us
     * which, without waiting for the cache to lapse.
     *
     * The key id comes from an unverified token, and the webhook endpoint is
     * public, so anyone can ask for this. Hence at most one refetch per
     * cooldown: within it, the cached keys are returned. And the cache is only
     * replaced after a successful fetch, so a failing endpoint does not take the
     * keys that still work down with it.
     *
     * @return array<string, Key>
     *
     * @throws PratiqueAssertionException
     */
    public function freshKeys(): array
    {
        if (!$this->cache->add(self::REFETCH_KEY, true, self::REFETCH_COOLDOWN_SECONDS)) {
            return $this->keys();
        }

        $document = $this->fetch();
        $this->cache->put(self::CACHE_KEY, $document, $this->cacheSeconds);

        return $this->parse($document);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PratiqueAssertionException
     */
    private function cachedDocument(): array
    {
        $cached = $this->cache->get(self::CACHE_KEY);

        if (is_array($cached)) {
            return self::asDocument($cached);
        }

        $document = $this->fetch();
        $this->cache->put(self::CACHE_KEY, $document, $this->cacheSeconds);

        return $document;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PratiqueAssertionException
     */
    private function fetch(): array
    {
        try {
            $response = $this->http->timeout(5)->get($this->jwksUrl);
        } catch (Throwable $exception) {
            throw PratiqueAssertionException::jwksUnavailable($exception->getMessage());
        }

        if (!$response->successful()) {
            throw PratiqueAssertionException::jwksUnavailable(
                'the endpoint returned HTTP ' . $response->status(),
            );
        }

        $document = $response->json();

        if (!is_array($document) || !isset($document['keys']) || !is_array($document['keys'])) {
            throw PratiqueAssertionException::jwksUnavailable('the document has no "keys" array');
        }

        return self::asDocument($document);
    }

    /**
     * Narrow a decoded JSON body to the shape the parser expects. The cache and
     * the HTTP client both hand back a loose array; this is the one place that
     * gets said.
     *
     * @param array<array-key, mixed> $document
     *
     * @return array<string, mixed>
     */
    private static function asDocument(array $document): array
    {
        $narrowed = [];

        foreach ($document as $key => $value) {
            $narrowed[(string) $key] = $value;
        }

        return $narrowed;
    }

    /**
     * @param array<string, mixed> $document
     *
     * @return array<string, Key>
     *
     * @throws PratiqueAssertionException
     */
    private function parse(array $document): array
    {
        try {
            // ES256 is the only algorithm the proxy signs with, and passing it
            // explicitly means a key advertising a weaker "alg" cannot talk us into
            // using it.
            $keys = JWK::parseKeySet($document, 'ES256');
        } catch (Throwable $exception) {
            throw PratiqueAssertionException::jwksUnavailable($exception->getMessage());
        }

        return $keys;
    }
}
