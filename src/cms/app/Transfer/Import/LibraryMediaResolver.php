<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Vendor\MediaLibrary\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function is_string;

/**
 * Resolves media bytes straight from the source media library (the direct cross-org
 * copy flow, where source and destination live on the same instance). The media item's
 * uuid identifies the original file, which is read from its disk in place — no zip
 * involved. Reads go through the filesystem so the media disk can be object storage.
 */
class LibraryMediaResolver implements MediaResolver
{
    /**
     * @param array<mixed> $mediaItem
     */
    public function resolve(array $mediaItem): ?string
    {
        $uuid = $mediaItem['uuid'] ?? null;

        if (!is_string($uuid) || !Str::isUuid($uuid)) {
            return null;
        }

        $media = Media::query()->where('uuid', $uuid)->first();

        if ($media === null) {
            return null;
        }

        return Storage::disk($media->disk)->get($media->getPathRelativeToRoot());
    }
}
