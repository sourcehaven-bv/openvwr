<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Vendor\MediaLibrary\Media;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function is_string;

/**
 * Resolves media bytes straight from the source media library (the direct cross-org
 * copy flow, where source and destination live on the same instance). The media item's
 * uuid identifies the original file, which is read from disk in place — no zip involved.
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

        $path = $media->getPath();

        return File::exists($path) ? File::get($path) : null;
    }
}
