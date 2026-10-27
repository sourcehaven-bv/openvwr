<?php

declare(strict_types=1);

namespace App\Filament\Tables\Columns;

use App\Config\Feature;
use App\Models\Contracts\Publishable;
use App\Services\StaticWebsite\Publication\PublicationStatus;
use App\Services\StaticWebsite\Publication\PublicationStatusResolver;
use Filament\Tables\Columns\IconColumn;
use WeakMap;

use function __;
use function app;

class PublicationStatusColumn extends IconColumn
{
    public static function make(string $name = 'publication_status'): static
    {
        // State, icon, colour and tooltip each ask for the status; resolve it
        // once per row.
        /** @var WeakMap<Publishable, PublicationStatus> $statuses */
        $statuses = new WeakMap();
        $status = static function (Publishable $record) use ($statuses): PublicationStatus {
            return $statuses[$record] ??= app(PublicationStatusResolver::class)->resolve($record);
        };

        return parent::make($name)
            ->label(__('static_website.public_from_section.title'))
            ->state(static fn (Publishable $record): string => $status($record)->state->value)
            ->icon(static fn (Publishable $record): string => $status($record)->state->getIcon())
            ->color(static fn (Publishable $record): string => $status($record)->state->getColor()->value)
            ->tooltip(static function (Publishable $record) use ($status): string {
                $publicationStatus = $status($record);

                return $publicationStatus->state->getLabel() . ': ' . $publicationStatus->getExplanation();
            })
            ->alignCenter()
            ->visible(Feature::publishingEnabled());
    }
}
