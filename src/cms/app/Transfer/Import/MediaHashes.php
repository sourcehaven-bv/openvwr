<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Models\Document;

use function is_array;
use function is_string;
use function sort;

/**
 * Compares a document's attachments by content_hash. Both the transfer bundle (a
 * serialized media block) and a live destination document expose their files' sha256
 * hashes; matching those tells a re-copy whether the bytes already agree without moving
 * any. A null/missing hash on either side is treated as "unknown, may differ" so callers
 * fall back to re-importing rather than wrongly assuming the files are identical.
 */
final readonly class MediaHashes
{
    /**
     * True when the destination document already carries exactly the source's attachments.
     *
     * @param array<string, mixed> $entity a serialized bundle entity (see EntitySerializer)
     */
    public function match(Document $document, array $entity): bool
    {
        $source = $this->fromMediaBlock($entity['media'] ?? []);
        $destination = $this->fromDocument($document);

        if ($source === null || $destination === null) {
            return false;
        }

        sort($source);
        sort($destination);

        return $source === $destination;
    }

    /**
     * Content hashes from a serialized media block, or null when any entry lacks a usable
     * hash.
     *
     * @return ?list<string>
     */
    public function fromMediaBlock(mixed $mediaItems): ?array
    {
        if (!is_array($mediaItems)) {
            return null;
        }

        $hashes = [];

        foreach ($mediaItems as $mediaItem) {
            $hash = is_array($mediaItem) ? ($mediaItem['content_hash'] ?? null) : null;

            if (!is_string($hash) || $hash === '') {
                return null;
            }

            $hashes[] = $hash;
        }

        return $hashes;
    }

    /**
     * Content hashes of a live document's attachments, or null when any lacks a usable hash.
     *
     * @return ?list<string>
     */
    public function fromDocument(Document $document): ?array
    {
        $hashes = [];

        foreach ($document->media as $mediaItem) {
            $hash = $mediaItem->content_hash;

            if (!is_string($hash) || $hash === '') {
                return null;
            }

            $hashes[] = $hash;
        }

        return $hashes;
    }
}
