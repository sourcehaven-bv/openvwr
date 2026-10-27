<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components\Section;

use App\Config\Feature;
use App\Models\Contracts\Publishable;
use App\Services\StaticWebsite\Publication\PublicationHistory;
use App\Services\StaticWebsite\Publication\PublicationStatusResolver;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ViewEntry;

use function __;
use function app;

class StaticWebsiteCheckSection extends Section
{
    public static function makeTable(): static
    {
        return parent::make(__('static_website.public_from_section.title'))
            ->description(__('static_website.public_from_section.subtitle'))
            ->schema(static function (Publishable $record): array {
                return [
                    ViewEntry::make('publication_status')
                        ->hiddenLabel()
                        ->view('filament.components.publication-status', [
                            'status' => app(PublicationStatusResolver::class)->resolve($record),
                            'history' => app(PublicationHistory::class)->forRecord($record),
                        ]),
                ];
            })
            ->visible(Feature::publishingEnabled());
    }
}
