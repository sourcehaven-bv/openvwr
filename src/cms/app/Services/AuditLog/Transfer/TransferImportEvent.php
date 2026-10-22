<?php

declare(strict_types=1);

namespace App\Services\AuditLog\Transfer;

use App\Models\User;
use App\Services\AuditLog\AuditEvent;
use App\Transfer\Import\TransferImportResult;

/**
 * A user imported register content into an organisation, either from an uploaded
 * transfer bundle or as a direct copy from another organisation on this instance.
 */
class TransferImportEvent implements AuditEvent
{
    public const string METHOD_BUNDLE = 'bundle';
    public const string METHOD_COPY = 'copy';

    /**
     * @param ?string $sourceOrganisationId verified for a copy; for a bundle only the
     *                                      unverified id claimed by the manifest
     * @param ?string $failedReason a fixed reason code, never raw exception text
     */
    public function __construct(
        public readonly ?User $user,
        public readonly string $method,
        public readonly string $destinationOrganisationId,
        public readonly ?string $sourceOrganisationId,
        public readonly ?TransferImportResult $result,
        public readonly ?string $failedReason = null,
    ) {
    }
}
