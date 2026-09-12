<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookException;
use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookHandler;
use App\Services\Authentication\Pratique\Webhooks\PratiqueWebhookVerifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseCode;

use function response;

/**
 * Receives lifecycle events from the proxy.
 *
 * This endpoint is unauthenticated by necessity — the proxy carries no session
 * when it calls us — so the JWT signature IS the authentication, and it is
 * checked before anything else happens.
 *
 * The status codes are a contract with the proxy's delivery loop, not decoration:
 *
 *   204 accepted (including an event we deliberately ignore) — do not retry
 *   403 the token is not one this proxy signed — retrying cannot help
 *   500 we failed to apply a genuine event — please retry
 *
 * Answering 4xx to a transient local failure would silently drop the event after
 * the proxy's ~20 attempts; answering 5xx to a forged one would invite an
 * attacker to make us retry. Hence the split.
 */
class PratiqueWebhookController extends Controller
{
    public function __construct(
        private readonly PratiqueWebhookVerifier $verifier,
        private readonly PratiqueWebhookHandler $handler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $event = $this->verifier->verify($request);
        } catch (PratiqueWebhookException $exception) {
            // Never echo the reason: this endpoint is public, and saying which
            // check failed helps someone probing it.
            Log::warning('Pratique webhook rejected', [
                'reason' => $exception->getMessage(),
            ]);

            return response()->noContent(ResponseCode::HTTP_FORBIDDEN);
        }

        $this->handler->handle($event);

        Log::info('Pratique webhook applied', [
            'event' => $event->type,
            'id' => $event->id,
        ]);

        return response()->noContent();
    }
}
