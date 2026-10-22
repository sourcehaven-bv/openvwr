<?php

declare(strict_types=1);

namespace App\Services\AuditLog\Transfer;

use App\Models\User;
use App\Services\AuditLog\AuditEvent;
use App\Transfer\TransferEntityType;

/**
 * A user exported register content from an organisation into a transfer bundle.
 */
class TransferExportEvent implements AuditEvent
{
    /**
     * @param list<string> $recordIds
     * @param array<string, list<string>> $selectedRelated
     */
    public function __construct(
        public readonly ?User $user,
        public readonly string $sourceOrganisationId,
        public readonly TransferEntityType $recordType,
        public readonly array $recordIds,
        public readonly array $selectedRelated,
        public readonly ?string $failedReason = null,
    ) {
    }
}
