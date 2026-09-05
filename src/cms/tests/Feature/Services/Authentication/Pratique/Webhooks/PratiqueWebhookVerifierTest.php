<?php

declare(strict_types=1);

use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookEvent;
use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookException;
use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookVerifier;
use Illuminate\Http\Request;
use Tests\Helpers\PratiqueTestHelper;

/*
 * The webhook endpoint is unauthenticated by necessity — the proxy holds no
 * session when it calls us — so this signature check IS the authentication.
 * These tests sign for real; a mocked verifier would keep passing even if the
 * production code stopped checking signatures at all.
 */

function webhookRequest(?string $token): Request
{
    return Request::create('/pratique/webhook', 'POST', [], [], [], [], $token ?? '');
}

function webhookVerifier(): PratiqueWebhookVerifier
{
    return app(PratiqueWebhookVerifier::class);
}

beforeEach(function (): void {
    $this->pratique = new PratiqueTestHelper();
    $this->pratique->publishJwks();
});

it('accepts a delivery the proxy signed', function (): void {
    $event = webhookVerifier()->verify(webhookRequest(
        $this->pratique->webhook(PratiqueWebhookEvent::SESSION_REVOKED, ['user_id' => 'usr_alice']),
    ));

    expect($event->type)->toBe(PratiqueWebhookEvent::SESSION_REVOKED)
        ->and($event->string('user_id'))->toBe('usr_alice');
});

it('rejects a delivery with no body', function (): void {
    webhookVerifier()->verify(webhookRequest(null));
})->throws(PratiqueWebhookException::class);

it('rejects a delivery signed by a key the proxy does not publish', function (): void {
    webhookVerifier()->verify(webhookRequest(
        PratiqueTestHelper::webhookFromForeignKey(PratiqueWebhookEvent::SESSION_REVOKED),
    ));
})->throws(PratiqueWebhookException::class);

it('rejects a delivery from an unexpected issuer', function (): void {
    webhookVerifier()->verify(webhookRequest($this->pratique->webhook(
        PratiqueWebhookEvent::SESSION_REVOKED,
        [],
        ['iss' => 'https://evil.example.com'],
    )));
})->throws(PratiqueWebhookException::class);

it('rejects an expired delivery', function (): void {
    webhookVerifier()->verify(webhookRequest($this->pratique->webhook(
        PratiqueWebhookEvent::SESSION_REVOKED,
        [],
        ['iat' => time() - 1200, 'exp' => time() - 600],
    )));
})->throws(PratiqueWebhookException::class);

/*
 * The textbook JWT forgery: declare the token unsigned and strip the signature.
 * The verifier must never take the token's own word for the algorithm.
 */
it('rejects an unsigned token claiming alg none', function (): void {
    $encode = static fn (array $part): string => rtrim(strtr(base64_encode(json_encode($part)), '+/', '-_'), '=');

    $token = $encode(['alg' => 'none', 'typ' => 'JWT']) . '.' . $encode([
        'id' => 'evt_forged',
        'event' => PratiqueWebhookEvent::SESSION_REVOKED,
        'iss' => PratiqueTestHelper::ISSUER,
        'exp' => time() + 600,
    ]) . '.';

    webhookVerifier()->verify(webhookRequest($token));
})->throws(PratiqueWebhookException::class);

it('rejects a body that is not a token at all', function (): void {
    webhookVerifier()->verify(webhookRequest('{"event":"session.revoked"}'));
})->throws(PratiqueWebhookException::class);

it('rejects a delivery missing its event type', function (): void {
    webhookVerifier()->verify(webhookRequest($this->pratique->webhook(
        PratiqueWebhookEvent::SESSION_REVOKED,
        [],
        ['event' => null],
    )));
})->throws(PratiqueWebhookException::class);

/*
 * A webhook token carries no `aud` — that is exactly why it needs its own
 * verifier rather than reusing the assertion path, which requires one.
 */
it('accepts a delivery even though it carries no audience', function (): void {
    $event = webhookVerifier()->verify(webhookRequest(
        $this->pratique->webhook(PratiqueWebhookEvent::MEMBERSHIP_DELETED, ['user_id' => 'usr_bob']),
    ));

    expect($event->type)->toBe(PratiqueWebhookEvent::MEMBERSHIP_DELETED);
});

it('rejects a delivery missing its id', function (): void {
    webhookVerifier()->verify(webhookRequest($this->pratique->webhook(
        PratiqueWebhookEvent::SESSION_REVOKED,
        [],
        ['id' => null],
    )));
})->throws(PratiqueWebhookException::class);

/*
 * A body that is not three dot-separated segments cannot name a key. It must be
 * refused rather than crash while trying to read one.
 */
it('rejects a token that is not three segments', function (): void {
    webhookVerifier()->verify(webhookRequest('only.two'));
})->throws(PratiqueWebhookException::class);

/* A malformed data claim reads as an empty body, never a crash. */
it('treats a non-object data claim as an empty body', function (): void {
    $event = webhookVerifier()->verify(webhookRequest($this->pratique->webhook(
        PratiqueWebhookEvent::SESSION_REVOKED,
        [],
        ['data' => 'not-an-object'],
    )));

    expect($event->string('user_id'))->toBeNull();
});
