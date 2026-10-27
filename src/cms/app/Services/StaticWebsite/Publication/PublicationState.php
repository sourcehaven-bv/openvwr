<?php

declare(strict_types=1);

namespace App\Services\StaticWebsite\Publication;

use App\Enums\StateColor;

use function __;
use function sprintf;
use function str_starts_with;

/**
 * Where a publishable record stands with respect to the public website.
 *
 * The NOT_* cases name the first condition that keeps a record off the site,
 * in the order the content generator applies them.
 */
enum PublicationState: string
{
    case PUBLISHED = 'published';
    case UPDATING = 'updating';
    case REMOVING = 'removing';
    case PENDING = 'pending';
    case NOT_CONFIRMED = 'not_confirmed';
    case SCHEDULED = 'scheduled';
    case NOT_PUBLISHING_DISABLED = 'not_publishing_disabled';
    case NOT_PUBLIC_FROM_MISSING = 'not_public_from_missing';
    case NOT_NO_ESTABLISHED_VERSION = 'not_no_established_version';
    case NOT_NO_PUBLIC_CONTENT = 'not_no_public_content';
    case NOT_ORGANISATION_NOT_ON_WEBSITE = 'not_organisation_not_on_website';
    case NOT_WEBSITE_SECTION_NOT_PUBLIC = 'not_website_section_not_public';

    /**
     * Whether the next build leaves the record out. PENDING and NOT_CONFIRMED
     * records do qualify; they are just not confirmed online yet.
     */
    public function keepsRecordOffline(): bool
    {
        return $this === self::SCHEDULED || str_starts_with($this->value, 'not_');
    }

    public function getColor(): StateColor
    {
        return match ($this) {
            self::PUBLISHED => StateColor::SUCCESS,
            self::UPDATING, self::PENDING, self::SCHEDULED => StateColor::INFO,
            self::REMOVING => StateColor::WARNING,
            self::NOT_CONFIRMED => StateColor::DANGER,
            default => StateColor::GRAY,
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PUBLISHED => 'heroicon-o-globe-alt',
            self::UPDATING, self::PENDING, self::REMOVING => 'heroicon-o-arrow-path',
            self::NOT_CONFIRMED => 'heroicon-o-exclamation-triangle',
            self::SCHEDULED => 'heroicon-o-clock',
            default => 'heroicon-o-eye-slash',
        };
    }

    public function getLabel(): string
    {
        return __(sprintf('static_website.publication_state.%s.label', $this->value));
    }
}
