<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use App\Support\ExternalLink;
use Filament\Infolists\Components\TextEntry;

/**
 * Renders a free-text value that is sometimes a URL: clickable when it is an
 * http(s) URL, plain text otherwise. See {@see ExternalLink} for the rules.
 *
 * Rendered through a dedicated view rather than TextEntry::url(), because
 * Filament emits target="_blank" without rel="noopener noreferrer" and offers
 * no hook to add it on the anchor itself.
 */
class ExternalLinkEntry extends TextEntry
{
    public static function make(string $name): static
    {
        return parent::make($name)
            ->view('filament.infolists.components.entries.external-link-entry')
            ->viewData([
                'isLinkable' => static fn (?string $state): bool => ExternalLink::isLinkable($state),
            ]);
    }
}
