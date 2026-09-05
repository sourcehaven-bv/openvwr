<?php

declare(strict_types=1);

use App\Enums\Authorization\Role;
use App\Http\Controllers\PratiqueWebhookController;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookEvent;
use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookHandler;
use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookVerifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\Helpers\PratiqueTestHelper;

/*
 * The status codes here are a contract with the proxy's delivery loop, not
 * decoration: 2xx stops retrying, 4xx is treated as a permanent rejection, and
 * 5xx asks for another attempt. Getting them the wrong way round either drops
 * real events after ~20 attempts or invites an attacker to make us retry.
 *
 * The controller is exercised directly rather than over HTTP: the route only
 * exists under the pratique driver, and the test environment runs builtin.
 */

function webhookController(): PratiqueWebhookController
{
    return new App\Http\Controllers\PratiqueWebhookController(
        app(PratiqueWebhookVerifier::class),
        app(PratiqueWebhookHandler::class),
    );
}

function postWebhook(?string $token): Response
{
    return webhookController()(
        Request::create('/pratique/webhook', 'POST', [], [], [], [], $token ?? ''),
    );
}

beforeEach(function (): void {
    $this->pratique = new PratiqueTestHelper();
    $this->pratique->publishJwks();
});

it('accepts a signed delivery and applies it', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme']);
    $user = User::factory()->create(['pratique_subject' => 'usr_alice']);
    $user->organisations()->attach($organisation);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);

    $response = postWebhook($this->pratique->webhook(
        PratiqueWebhookEvent::MEMBERSHIP_DELETED,
        ['user_id' => 'usr_alice', 'org_slug' => 'acme'],
    ));

    expect($response->getStatusCode())->toBe(204)
        ->and($user->organisationRoles()->count())->toBe(0);
});

/*
 * A forged delivery gets 403, never 5xx: retrying cannot turn a bad signature
 * into a good one, and a 5xx would just make the proxy keep coming back.
 */
it('refuses a delivery it cannot verify', function (): void {
    $response = postWebhook(PratiqueTestHelper::webhookFromForeignKey(
        PratiqueWebhookEvent::SESSION_REVOKED,
        ['user_id' => 'usr_alice'],
    ));

    expect($response->getStatusCode())->toBe(403);
});

it('refuses an empty delivery', function (): void {
    expect(postWebhook(null)->getStatusCode())->toBe(403);
});

/*
 * An event this app does not act on is still a successful delivery. Answering
 * with an error would make the proxy retry something we will never want.
 */
it('accepts an event it does not act on', function (): void {
    $response = postWebhook($this->pratique->webhook('invitation.created', ['invitation_id' => 'inv_1']));

    expect($response->getStatusCode())->toBe(204);
});

/*
 * A forged delivery must not change anything before it is rejected — the
 * signature is checked first, so no state is touched.
 */
it('changes nothing when the delivery is forged', function (): void {
    $organisation = Organisation::factory()->create(['slug' => 'acme']);
    $user = User::factory()->create(['pratique_subject' => 'usr_alice']);
    $user->organisations()->attach($organisation);
    $user->assignOrganisationRole(Role::PRIVACY_OFFICER, $organisation);

    postWebhook(PratiqueTestHelper::webhookFromForeignKey(
        PratiqueWebhookEvent::MEMBERSHIP_DELETED,
        ['user_id' => 'usr_alice', 'org_slug' => 'acme'],
    ));

    expect($user->organisationRoles()->count())->toBe(1);
});
