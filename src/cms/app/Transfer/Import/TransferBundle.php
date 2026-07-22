<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use function is_array;
use function is_string;

readonly class TransferBundle
{
    /**
     * @param array<string, mixed> $manifest
     * @param array<string, array<string, mixed>> $entities entity data keyed by bundle uuid
     */
    public function __construct(
        public array $manifest,
        public array $entities,
    ) {
    }

    public function sourceOrganisationName(): string
    {
        $sourceOrganisation = $this->manifest['source_organisation'] ?? null;

        if (!is_array($sourceOrganisation)) {
            return '';
        }

        $name = $sourceOrganisation['name'] ?? null;

        return is_string($name) ? $name : '';
    }

    public function exportedAt(): string
    {
        $exportedAt = $this->manifest['exported_at'] ?? null;

        return is_string($exportedAt) ? $exportedAt : '';
    }
}
