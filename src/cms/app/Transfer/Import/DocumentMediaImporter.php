<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Enums\Media\MediaGroup;
use App\Models\Document;

use function in_array;
use function is_array;
use function is_string;

/**
 * Writes a document's attachments during an import/copy. Splits two cases: a freshly
 * created document takes all the source's media, while an overwritten one only re-imports
 * when the bytes actually changed (compared by content_hash) — leaving matching files in
 * place so a re-copy of unchanged content moves no bytes.
 */
readonly class DocumentMediaImporter
{
    public function __construct(
        private MediaHashes $mediaHashes,
    ) {
    }

    /**
     * Attach all of the source's media to a newly created document.
     *
     * @param array<string, mixed> $entity
     */
    public function import(Document $document, array $entity, MediaResolver $mediaResolver): void
    {
        $this->addMedia($document, $entity, $mediaResolver);
    }

    /**
     * Bring an overwritten document's attachments in line with the source. When the
     * destination already holds exactly the source's files the bytes are left untouched;
     * otherwise the affected collections are cleared and re-imported so a changed source
     * file is not left stale.
     *
     * @param array<string, mixed> $entity
     */
    public function sync(Document $document, array $entity, MediaResolver $mediaResolver): void
    {
        if ($this->mediaHashes->match($document, $entity)) {
            return;
        }

        foreach ($this->incomingCollections($entity) as $collection) {
            $document->clearMediaCollection($collection);
        }

        $this->addMedia($document, $entity, $mediaResolver);
    }

    /**
     * @param array<string, mixed> $entity
     */
    private function addMedia(Document $document, array $entity, MediaResolver $mediaResolver): void
    {
        $mediaItems = $entity['media'] ?? [];

        if (!is_array($mediaItems)) {
            return;
        }

        foreach ($mediaItems as $mediaItem) {
            $this->addMediaItem($document, $mediaItem, $mediaResolver);
        }
    }

    private function addMediaItem(Document $document, mixed $mediaItem, MediaResolver $mediaResolver): void
    {
        if (!is_array($mediaItem)) {
            return;
        }

        $fileName = $mediaItem['file_name'] ?? null;
        $collectionName = $mediaItem['collection_name'] ?? null;

        if (!is_string($fileName) || !is_string($collectionName)) {
            return;
        }

        $contents = $mediaResolver->resolve($mediaItem);

        if ($contents === null) {
            return;
        }

        $name = $mediaItem['name'] ?? null;

        $document->addMediaFromString($contents)
            ->usingFileName($fileName)
            ->usingName(is_string($name) ? $name : $fileName)
            ->toMediaCollection($collectionName);
    }

    /**
     * Collections touched by the incoming media block. Only these are cleared before
     * re-import, so attachments the source did not carry are left in place. Falls back to
     * attachments so an empty source media block still clears a stale destination file.
     *
     * @param array<string, mixed> $entity
     *
     * @return list<string>
     */
    private function incomingCollections(array $entity): array
    {
        $mediaItems = $entity['media'] ?? [];

        if (!is_array($mediaItems)) {
            return [MediaGroup::ATTACHMENTS->value];
        }

        $collections = [];

        foreach ($mediaItems as $mediaItem) {
            $collection = is_array($mediaItem) ? ($mediaItem['collection_name'] ?? null) : null;

            if (is_string($collection) && !in_array($collection, $collections, true)) {
                $collections[] = $collection;
            }
        }

        return $collections === [] ? [MediaGroup::ATTACHMENTS->value] : $collections;
    }
}
