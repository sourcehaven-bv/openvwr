<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use Illuminate\Support\Str;

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

    /**
     * The source organisation id claimed by the manifest. The bundle is uploaded by
     * the user and not signed, so this is unverified; anything that is not a UUID is dropped.
     */
    public function sourceOrganisationId(): ?string
    {
        $id = $this->sourceOrganisation()['id'] ?? null;

        return is_string($id) && Str::isUuid($id) ? $id : null;
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
