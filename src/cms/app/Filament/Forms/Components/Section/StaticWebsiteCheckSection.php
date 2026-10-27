<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Section;

use App\Config\Feature;
use App\Models\Contracts\Publishable;
use App\Services\StaticWebsite\Publication\PublicationHistory;
use App\Services\StaticWebsite\Publication\PublicationStatusResolver;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;

use function __;
use function app;

class StaticWebsiteCheckSection extends Section
{
    public static function makeTable(): static
    {
        return parent::make(__('static_website.public_from_section.title'))
            ->description(__('static_website.public_from_section.subtitle'))
            ->schema(static function (?Publishable $record): array {
                if ($record === null) {
                    return [];
                }

                return [
                    Placeholder::make('static_website_publication_status')
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
