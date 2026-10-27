<?php

declare(strict_types=1);

namespace App\Services\Authentication\Pratique\Webhooks;

use RuntimeException;

use function sprintf;

/**
 * A webhook delivery could not be trusted, or could not be understood.
 *
 * As with assertions the reason is logged but never returned to the caller: a
 * receiver that says *which* check failed helps someone probing it.
 */
class PratiqueWebhookException extends RuntimeException
{
    public static function missingBody(): self
    {
        return new self('The webhook request carried no signed token.');
    }

    public static function notVerified(string $reason): self
    {
        return new self(sprintf('The webhook token could not be verified: %s', $reason));
    }

    public static function malformed(string $claim): self
    {
        return new self(sprintf('The webhook payload is missing or malformed at "%s".', $claim));
    }
}
