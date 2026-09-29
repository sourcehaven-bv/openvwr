<?php

declare(strict_types=1);

use App\Services\Authentication\Pratique\PratiqueAssertionException;
use App\Services\Authentication\Pratique\PratiqueAssertionVerifier;
use Illuminate\Http\Request;
use Tests\Helpers\PratiqueTestHelper;

/*
 * The verifier is this application's entire trust boundary under the pratique
 * driver: the proxy strips inbound Authorization headers and sets its own, so a
 * validly signed assertion is the only thing separating an authenticated request
 * from an arbitrary one.
 *
 * These tests therefore sign for real. A mocked signature check would keep
 * passing even if the production code stopped checking signatures at all.
 */

function pratiqueRequest(?string $token): Request
{
    $request = Request::create('/test-org/dashboard', 'GET');

    if ($token !== null) {
        $request->headers->set('Authorization', 'Bearer ' . $token);
    }

    return $request;
}

function pratiqueVerifier(): PratiqueAssertionVerifier
{
    return app(PratiqueAssertionVerifier::class);
}

beforeEach(function (): void {
    $this->pratique = new PratiqueTestHelper();
    $this->pratique->publishJwks();
});

it('accepts a well-formed assertion', function (): void {
    $assertion = pratiqueVerifier()->verify(pratiqueRequest($this->pratique->assertion([
        'sub' => 'usr_alice',
        'email' => 'alice@example.org',
        'org_slug' => 'acme',
        'roles' => ['privacy-officer'],
    ])));

    expect($assertion->subject)->toBe('usr_alice')
        ->and($assertion->email)->toBe('alice@example.org')
        ->and($assertion->organisationSlug)->toBe('acme')
        ->and($assertion->roles)->toBe(['privacy-officer']);
});

it('rejects a request with no assertion at all', function (): void {
    pratiqueVerifier()->verify(pratiqueRequest(null));
})->throws(PratiqueAssertionException::class);

it('rejects an Authorization header that is not a bearer token', function (): void {
    $request = Request::create('/test-org/dashboard', 'GET');
    $request->headers->set('Authorization', 'Basic ' . base64_encode('user:pass'));

    pratiqueVerifier()->verify($request);
})->throws(PratiqueAssertionException::class);

it('accepts the bearer scheme in any casing', function (): void {
    $request = Request::create('/test-org/dashboard', 'GET');
    $request->headers->set('Authorization', 'bEaReR ' . $this->pratique->assertion());

    expect(pratiqueVerifier()->verify($request)->subject)->toBe('usr_01TESTSUBJECT');
});

/*
 * The confused-deputy guard. The proxy mints one audience per upstream, so an
 * assertion for a *different* app is a perfectly valid, correctly signed token —
 * it simply is not for us. Accepting it would let another upstream's users in.
 */
it('rejects an assertion minted for a different upstream', function (): void {
    pratiqueVerifier()->verify(pratiqueRequest(
        $this->pratique->assertion(['aud' => 'app://some-other-service']),
    ));
})->throws(PratiqueAssertionException::class);

it('rejects an assertion from an unexpected issuer', function (): void {
    pratiqueVerifier()->verify(pratiqueRequest(
        $this->pratique->assertion(['iss' => 'https://evil.example.com']),
    ));
})->throws(PratiqueAssertionException::class);

it('rejects an expired assertion', function (): void {
    pratiqueVerifier()->verify(pratiqueRequest($this->pratique->assertion([
        'iat' => time() - 1200,
        'nbf' => time() - 1200,
        'exp' => time() - 600,
    ])));
})->throws(PratiqueAssertionException::class);

it('rejects an assertion that is not valid yet', function (): void {
    pratiqueVerifier()->verify(pratiqueRequest($this->pratique->assertion([
        'nbf' => time() + 600,
        'exp' => time() + 1200,
    ])));
})->throws(PratiqueAssertionException::class);

/*
 * Clock drift between the proxy host and this one must not read as forgery. The
 * leeway is small relative to the ~9 minute assertion lifetime.
 */
it('tolerates small clock drift', function (): void {
    $assertion = pratiqueVerifier()->verify(pratiqueRequest($this->pratique->assertion([
        'exp' => time() - 5,
    ])));

    expect($assertion->subject)->toBe('usr_01TESTSUBJECT');
});

it('rejects an assertion signed by a key the proxy does not publish', function (): void {
    pratiqueVerifier()->verify(pratiqueRequest(
        PratiqueTestHelper::assertionFromForeignKey(),
    ));
})->throws(PratiqueAssertionException::class);

it('rejects a token that is not a JWT at all', function (): void {
    pratiqueVerifier()->verify(pratiqueRequest('not-a-jwt'));
})->throws(PratiqueAssertionException::class);

/*
 * "alg: none" is the textbook JWT forgery: strip the signature and declare the
 * token unsigned. The verifier must never honour the token's own opinion about
 * which algorithm to use.
 */
it('rejects an unsigned token claiming alg none', function (): void {
    $payload = [
        'iss' => PratiqueTestHelper::ISSUER,
        'aud' => PratiqueTestHelper::AUDIENCE,
        'sub' => 'usr_forged',
        'email' => 'forged@example.org',
        'org_id' => 'org_01TESTORG',
        'org_slug' => 'test-org',
        'exp' => time() + 600,
    ];

    $encode = static fn (array $part): string => rtrim(strtr(base64_encode(json_encode($part)), '+/', '-_'), '=');
    $token = $encode(['alg' => 'none', 'typ' => 'JWT']) . '.' . $encode($payload) . '.';

    pratiqueVerifier()->verify(pratiqueRequest($token));
})->throws(PratiqueAssertionException::class);

/*
 * A machine principal can hold a valid assertion for this audience, but every
 * policy in this app is written around a person. Refuse rather than half-support.
 */
it('rejects a non-user principal', function (string $principalType): void {
    pratiqueVerifier()->verify(pratiqueRequest(
        $this->pratique->assertion(['principal_type' => $principalType]),
    ));
})->with(['service', 'pat', 'app'])->throws(PratiqueAssertionException::class);

it('rejects an assertion missing a required claim', function (string $claim): void {
    pratiqueVerifier()->verify(pratiqueRequest(
        $this->pratique->assertion([$claim => null]),
    ));
})->with(['sub', 'email', 'org_id', 'org_slug'])->throws(PratiqueAssertionException::class);

/*
 * A member with no role in the active organisation is a valid state (an SSO
 * just-in-time signup, for instance): they authenticate, and every policy denies.
 */
it('accepts an assertion with no roles', function (): void {
    expect(pratiqueVerifier()->verify(pratiqueRequest($this->pratique->assertion(['roles' => []])))->roles)
        ->toBe([]);
});

/*
 * A token whose header is not JSON cannot name a key. It must be refused rather
 * than crash while trying to read one.
 */
it('rejects a token whose header is not readable', function (): void {
    pratiqueVerifier()->verify(pratiqueRequest('!!!not-base64!!!.eyJhIjoxfQ.sig'));
})->throws(PratiqueAssertionException::class);

/*
 * Roles arriving as something other than a list (a string, say) must read as "no
 * roles" rather than crash — a malformed claim should deny, not 500.
 */
it('treats a malformed roles claim as no roles', function (): void {
    expect(pratiqueVerifier()->verify(pratiqueRequest(
        $this->pratique->assertion(['roles' => 'privacy-officer']),
    ))->roles)->toBe([]);
});

/* Non-string entries inside the roles list are ignored rather than fatal. */
it('ignores non-string entries in the roles claim', function (): void {
    expect(pratiqueVerifier()->verify(pratiqueRequest(
        $this->pratique->assertion(['roles' => ['privacy-officer', 42, null]]),
    ))->roles)->toBe(['privacy-officer']);
});
