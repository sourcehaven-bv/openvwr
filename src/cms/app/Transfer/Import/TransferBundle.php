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

    public function sourceOrganisationId(): ?string
    {
        $id = $this->sourceOrganisation()['id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    public function sourceOrganisationName(): string
    {
        $name = $this->sourceOrganisation()['name'] ?? null;

        return is_string($name) ? $name : '';
    }

    /**
     * @return array<mixed>
     */
    private function sourceOrganisation(): array
    {
        $sourceOrganisation = $this->manifest['source_organisation'] ?? null;

        return is_array($sourceOrganisation) ? $sourceOrganisation : [];
    }

    public function exportedAt(): string
    {
        $exportedAt = $this->manifest['exported_at'] ?? null;

        return is_string($exportedAt) ? $exportedAt : '';
    }
}
