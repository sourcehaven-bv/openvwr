<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Section;

use App\Config\Feature;
use App\Models\Contracts\Publishable;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Collection;

use function __;
use function sprintf;

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

                return self::getSchema($record);
            })
            ->visible(Feature::publishingEnabled());
    }

    /**
     * @return array<Component>
     */
    private static function getSchema(Publishable $record): array
    {
        $schema = [];

        $view = $record->isPublished() ? 'published' : 'unpublished';
        $schema[] = Placeholder::make('static_website_public_state')
            ->label(__('static_website.public_from_section.public_state'))
            ->view(sprintf('filament.forms.components.section.static_website_check_section.%s', $view));

        /** @var Collection<int, string> $snapshotIds */
        $snapshotIds = $record->snapshots()->pluck('id');

        $staticWebsiteSnapshotEntries = $record->getStaticWebsiteSnapshotEntries($snapshotIds);
        if ($staticWebsiteSnapshotEntries->isNotEmpty()) {
            $staticWebsiteHistoryItems = new Collection();

            foreach ($staticWebsiteSnapshotEntries as $staticWebsiteSnapshotEntry) {
                $format = 'd-m-Y H:i';

                if ($staticWebsiteSnapshotEntry->end_date === null) {
                    $staticWebsiteHistoryItems->push(__('static_website.public_from_section.public_history_since', [
                        'start' => $staticWebsiteSnapshotEntry->start_date->format($format),
                    ]));
                    continue;
                }

                $staticWebsiteHistoryItems->push(__('static_website.public_from_section.public_history_from_to', [
                    'start' => $staticWebsiteSnapshotEntry->start_date->format($format),
                    'end' => $staticWebsiteSnapshotEntry->end_date->format($format),
                ]));
            }

            $schema[] = Placeholder::make('static_website_snapshot_entry')
                ->label(__('static_website.public_from_section.public_history'))
                ->view('filament.forms.components.section.static-website-history', ['items' => $staticWebsiteHistoryItems]);
        }

        return $schema;
    }
}
