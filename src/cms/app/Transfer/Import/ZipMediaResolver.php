<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use function is_string;

/**
 * Resolves media bytes from a transfer zip (the import-from-file flow).
 */
readonly class ZipMediaResolver implements MediaResolver
{
    public function __construct(
        private BundleReader $bundleReader,
        private string $zipPath,
    ) {
    }

    /**
     * @param array<mixed> $mediaItem
     */
    public function resolve(array $mediaItem): ?string
    {
        $zipEntryPath = $mediaItem['zip_path'] ?? null;

        if (!is_string($zipEntryPath)) {
            return null;
        }

        return $this->bundleReader->readMedia($this->zipPath, $zipEntryPath);
    }
}
