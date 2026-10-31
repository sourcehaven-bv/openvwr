<?php

declare(strict_types=1);

namespace App\Services\AuditLog\Register;

use App\Models\User;
use App\Services\AuditLog\AuditEvent;

/**
 * Links from a register record to shared records were removed, for example when
 * a user switched off "has processors". The linked records themselves remain.
 */
class LinksDetachedEvent implements AuditEvent
{
    /**
     * @param list<string> $relatedIds
     */
    public function __construct(
        public readonly ?User $user,
        public readonly string $organisationId,
        public readonly string $recordType,
        public readonly string $recordId,
        public readonly string $relation,
        public readonly array $relatedIds,
    ) {
    }
}
