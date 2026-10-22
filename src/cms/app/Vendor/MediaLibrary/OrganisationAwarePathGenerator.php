<?php

declare(strict_types=1);

namespace App\Vendor\MediaLibrary;

use App\Components\Uuid\UuidInterface;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

use function is_string;
use function sprintf;

class OrganisationAwarePathGenerator extends DefaultPathGenerator
{
    protected function getBasePath(SpatieMedia $media): string
    {
        // Read raw: Media declares ?string, but HasOrganisation casts it to a Uuid.
        $organisationId = $media->getAttribute('organisation_id');

        if (is_string($organisationId)) {
            return sprintf('%s/%s/%s', $organisationId, $media->collection_name, $media->uuid);
        }
        if ($organisationId instanceof UuidInterface) {
            return sprintf('%s/%s/%s', $organisationId->toString(), $media->collection_name, $media->uuid);
        }

        return sprintf('%s/%s', $media->collection_name, $media->uuid);
    }
}
