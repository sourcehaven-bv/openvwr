<?php

declare(strict_types=1);

namespace App\Manual;

use App\Config\Feature;

/**
 * The optional features a manual topic or task can depend on.
 *
 * A topic or task carries at most one gate. When the matching feature flag is
 * off the content is dropped from the manual entirely: it never reaches the
 * page, the navigation, the backlinks or the search index, so it cannot be
 * reached by scrolling, by anchor or by url.
 */
enum FeatureGate: string
{
    case PUBLISHING = 'FEATURE_PUBLISHING';
    case WPG = 'FEATURE_WPG';

    public function enabled(): bool
    {
        return match ($this) {
            self::PUBLISHING => Feature::publishingEnabled(),
            self::WPG => Feature::wpgEnabled(),
        };
    }
}
