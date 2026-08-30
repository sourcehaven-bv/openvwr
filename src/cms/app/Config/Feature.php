<?php

declare(strict_types=1);

namespace App\Config;

class Feature
{
    /**
     * Publishing records to the public static website. When this is off the
     * publishing user interface is hidden; the records, snapshots, exports and
     * the static website generation itself keep working.
     */
    public static function publishingEnabled(): bool
    {
        return Config::boolean('features.publishing');
    }
}
