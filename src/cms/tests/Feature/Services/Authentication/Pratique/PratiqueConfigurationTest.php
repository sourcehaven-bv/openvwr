<?php

declare(strict_types=1);

use App\Services\Authentication\Pratique\JwksProvider;
use App\Services\Authentication\Pratique\PratiqueAssertionException;
use App\Services\Authentication\Pratique\PratiqueAssertionVerifier;
use Tests\Helpers\PratiqueTestHelper;

/*
 * The issuer, audience and JWKS URL define whom this application trusts. There is
 * no safe default for any of them: guessing would mean verifying assertions
 * against the wrong authority, so a missing value has to stop the app rather than
 * weaken the check.
 */

/*
 * These tests deliberately break the configuration, and both the config and the
 * resolved singletons outlive a single test. Without restoring them, a later
 * test in the same worker resolves a verifier built from null settings — which
 * is exactly how this file made an unrelated middleware test fail in CI while
 * passing in isolation.
 */
afterEach(function (): void {
    config([
        'auth.pratique.issuer' => PratiqueTestHelper::ISSUER,
        'auth.pratique.audience' => PratiqueTestHelper::AUDIENCE,
        'auth.pratique.jwks_url' => PratiqueTestHelper::JWKS_URL,
    ]);

    app()->forgetInstance(PratiqueAssertionVerifier::class);
    app()->forgetInstance(JwksProvider::class);
});

it('refuses to build the verifier without its trust settings', function (string $setting): void {
    config(['auth.pratique.' . $setting => null]);

    app()->forgetInstance(PratiqueAssertionVerifier::class);
    app()->forgetInstance(JwksProvider::class);

    app(PratiqueAssertionVerifier::class);
})->with(['issuer', 'audience', 'jwks_url'])->throws(PratiqueAssertionException::class);

it('describes which setting is missing', function (): void {
    config(['auth.pratique.audience' => null]);

    app()->forgetInstance(PratiqueAssertionVerifier::class);
    app()->forgetInstance(JwksProvider::class);

    try {
        app(PratiqueAssertionVerifier::class);
    } catch (PratiqueAssertionException $exception) {
        expect($exception->getMessage())->toContain('auth.pratique.audience');

        return;
    }

    $this->fail('A missing audience should have stopped the verifier being built.');
});
