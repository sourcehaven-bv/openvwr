<?php

declare(strict_types=1);

use App\Services\Authentication\Pratique\JwksProvider;
use App\Services\Authentication\Pratique\PratiqueAssertionException;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as Http;
use Tests\Helpers\PratiqueTestHelper;

/*
 * Pratique rotates its signing key on a schedule and publishes the current and
 * previous key together. A verifier that pins one key keeps working right up
 * until a rotation and then fails for everyone at once, so the fetch-and-cache
 * behaviour is worth testing on its own.
 */

function jwksProvider(Http $http): JwksProvider
{
    return new JwksProvider($http, app(Cache::class), PratiqueTestHelper::JWKS_URL, 300);
}

beforeEach(function (): void {
    app(Cache::class)->forget('pratique:jwks');
});

it('fetches and parses the published keys', function (): void {
    $helper = new PratiqueTestHelper();

    $http = new Http();
    $http->fake([PratiqueTestHelper::JWKS_URL => $http::response($helper->jwks())]);

    $publishedKid = $helper->jwks()['keys'][0]['kid'];

    expect(jwksProvider($http)->keys())->toHaveKey($publishedKid);
});

it('serves later calls from the cache instead of refetching', function (): void {
    $helper = new PratiqueTestHelper();

    $http = new Http();
    $http->fake([PratiqueTestHelper::JWKS_URL => $http::response($helper->jwks())]);

    $provider = jwksProvider($http);
    $provider->keys();
    $provider->keys();
    $provider->keys();

    $http->assertSentCount(1);
});

/*
 * The rotation path: asked for keys again after an explicit refresh, the provider
 * must go back to the endpoint rather than answer from a stale cache.
 */
it('refetches when asked for fresh keys', function (): void {
    $helper = new PratiqueTestHelper();

    $http = new Http();
    $http->fake([PratiqueTestHelper::JWKS_URL => $http::response($helper->jwks())]);

    $provider = jwksProvider($http);
    $provider->keys();
    $provider->freshKeys();

    $http->assertSentCount(2);
});

it('reports an unreachable endpoint rather than returning no keys', function (): void {
    $http = new Http();
    $http->fake([PratiqueTestHelper::JWKS_URL => $http::response('', 503)]);

    jwksProvider($http)->keys();
})->throws(PratiqueAssertionException::class);

it('rejects a document that is not a key set', function (): void {
    $http = new Http();
    $http->fake([PratiqueTestHelper::JWKS_URL => $http::response(['not' => 'a key set'])]);

    jwksProvider($http)->keys();
})->throws(PratiqueAssertionException::class);

/* A refused connection must surface as "keys unavailable", not an unhandled error. */
it('reports a connection failure rather than throwing raw', function (): void {
    $http = new Http();
    $http->fake(fn (): never => throw new ConnectionException('connection refused'));

    jwksProvider($http)->keys();
})->throws(PratiqueAssertionException::class);

/*
 * A document shaped like a key set but holding nothing usable (an RSA key, say,
 * where only P-256 is signed with) must fail rather than yield an empty key set
 * that would make every signature check quietly unverifiable.
 */
it('rejects a key set with no usable key', function (): void {
    $http = new Http();
    $http->fake([PratiqueTestHelper::JWKS_URL => $http::response(['keys' => [['kty' => 'oct']]])]);

    jwksProvider($http)->keys();
})->throws(PratiqueAssertionException::class);
