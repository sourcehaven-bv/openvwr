<?php

declare(strict_types=1);

namespace App\Services\AuditLog\Transfer;

use Throwable;

use function class_basename;

/**
 * Turns a transfer failure into a fixed reason code for the audit log. Exception messages
 * can carry content from an uploaded bundle (such as zip entry names), so only the exception
 * class is recorded; the message belongs in the regular error log.
 */
final class TransferFailureReason
{
    public static function from(?Throwable $exception): string
    {
        return $exception === null ? 'unknown' : class_basename($exception);
    }
}
