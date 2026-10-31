<?php

declare(strict_types=1);

namespace App\Services\Authentication\Pratique\Webhooks;

use stdClass;

use function is_array;
use function is_string;

/**
 * One verified lifecycle event from the proxy.
 *
 * Only ever constructed from a payload whose signature and issuer have already
 * been checked, so holding one means "the proxy really sent this".
 *
 * The payload is deliberately thin: events carry identifiers and a `fields` list,
 * not values. That is a feature, not a shortcoming — a receiver is expected to
 * treat an event as "something about X changed, go look", never as data to write
 * straight through. See PratiqueWebhookController for why that matters here.
 */
final readonly class PratiqueWebhookEvent
{
    // Event types this application acts on. Pratique emits more (invitations,
    // service accounts, organisations); those are ignored rather than rejected,
    // because a receiver that 400s on an event it simply does not care about
    // would make the proxy retry it ~20 times before dead-lettering.
    public const USER_UPDATED = 'user.updated';
    public const MEMBERSHIP_UPDATED = 'membership.updated';
    public const MEMBERSHIP_DELETED = 'membership.deleted';
    public const SESSION_REVOKED = 'session.revoked';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public string $id,
        public string $type,
        public array $data,
    ) {
    }

    /**
     * @param array<string, mixed> $claims a payload that has already been verified
     *
     * @throws PratiqueWebhookException
     */
    public static function fromClaims(array $claims): self
    {
        $id = $claims['id'] ?? null;
        $type = $claims['event'] ?? null;

        if (!is_string($id) || $id === '') {
            throw PratiqueWebhookException::malformed('id');
        }

        if (!is_string($type) || $type === '') {
            throw PratiqueWebhookException::malformed('event');
        }

        return new self($id, $type, self::body($claims['data'] ?? null));
    }

    /**
     * The event body as an array.
     *
     * JWT decoding yields nested objects rather than arrays, so `data` arrives as
     * a stdClass and has to be flattened before anything can read it. Anything
     * that is neither becomes an empty body: a malformed payload must read as
     * "nothing to act on", never as a crash on a public endpoint.
     *
     * @return array<string, mixed>
     */
    private static function body(mixed $data): array
    {
        if ($data instanceof stdClass) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            return [];
        }

        $body = [];
        foreach ($data as $key => $value) {
            $body[(string) $key] = $value;
        }

        return $body;
    }

    /** A string field from the event body, or null when absent or the wrong type. */
    public function string(string $key): ?string
    {
        $value = $this->data[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
