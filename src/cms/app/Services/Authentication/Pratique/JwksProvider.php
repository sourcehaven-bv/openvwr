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
     * @return array<string, Key>
     *
     * @throws PratiqueAssertionException
     */
    public function freshKeys(): array
    {
        $this->cache->forget(self::CACHE_KEY);

        return $this->keys();
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
