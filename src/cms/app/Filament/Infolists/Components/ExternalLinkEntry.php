<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Support\ExternalLink;
use Filament\Infolists\Components\TextEntry;

use function trim;

/**
 * Renders a free-text value that is sometimes a URL: clickable when it is an
 * http(s) URL, plain text otherwise. See {@see ExternalLink} for the rules.
 */
class ExternalLinkEntry extends TextEntry
{
    public static function make(string $name): static
    {
        return parent::make($name)
            ->url(static function (?string $state): ?string {
                $state = trim((string) $state);

                return ExternalLink::isLinkable($state) ? $state : null;
            })
            ->openUrlInNewTab(static fn (?string $state): bool => ExternalLink::isLinkable($state));
    }
}
