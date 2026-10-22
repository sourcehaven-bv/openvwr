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

    /** The organisation the bundle was exported from, as recorded in its manifest. */
    public ?string $sourceOrganisationId = null;

    public function __construct(
        public int $created = 0,
        public int $overwritten = 0,
        public int $skipped = 0,
    ) {
    }
}
