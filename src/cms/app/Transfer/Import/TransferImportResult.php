<?php

declare(strict_types=1);

namespace App\Transfer\Import;

class TransferImportResult
{
    /**
     * Records this import created or overwrote, for the audit log.
     *
     * @var list<array{type: string, id: string, action: 'created'|'overwritten'}>
     */
    public array $records = [];

    /**
     * The organisation the bundle claims to come from. Unverified: taken from the
     * manifest of an uploaded bundle (see TransferBundle::sourceOrganisationId()).
     */
    public ?string $claimedSourceOrganisationId = null;

    public function __construct(
        public int $created = 0,
        public int $overwritten = 0,
        public int $skipped = 0,
    ) {
    }
}
