<?php

declare(strict_types=1);

namespace App\Config;

class Feature
{
    /**
     * Publishing records to the public static website. When this is off the
     * publishing user interface is hidden and static website generation is
     * disabled; the records, snapshots and exports keep working.
     */
    public static function publishingEnabled(): bool
    {
        return Config::boolean('features.publishing');
    }

    /**
     * The WPG register. When this is off the WPG user interface is hidden: the
     * register and its lookup list disappear from the navigation and cannot be
     * reached by url, and WPG stops showing up on the dashboard, in relation
     * managers and in the pickers of the other registers. The records,
     * snapshots, imports, exports and transfers keep working.
     */
    public static function wpgEnabled(): bool
    {
        return Config::boolean('features.wpg');
    }
}
