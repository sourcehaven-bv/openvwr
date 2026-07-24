<?php

declare(strict_types=1);

namespace App\Transfer\Import;

/**
 * Resolves the binary contents of a document's media item during an import/copy,
 * abstracting over where the bytes live (a transfer zip, or the source media library
 * on the same instance for a direct cross-org copy).
 */
interface MediaResolver
{
    /**
     * @param array<mixed> $mediaItem a serialized media entry (see EntitySerializer::serializeMedia)
     */
    public function resolve(array $mediaItem): ?string;
}
