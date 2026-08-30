<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components\Section;

use Filament\Schemas\Components\Section;
use App\Config\Feature;
use App\Models\Contracts\Publishable;
use App\Services\DateFormatService;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Collection;
use Webmozart\Assert\Assert;

use function __;

class StaticWebsiteCheckSection extends Section
{
    public static function makeTable(): static
    {
        return parent::make(__('static_website.public_from_section.title'))
            ->description(__('static_website.public_from_section.subtitle'))
            ->schema(static function (Publishable $record): array {
                return self::getSchema($record);
            })
            ->visible(Feature::publishingEnabled());
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    private static function getSchema(Publishable $record): array
    {
        $schema = [];

        $schema[] = TextEntry::make('id')
            ->label(__('static_website.public_from_section.public_state'))
            ->badge()
            ->color(static function () use ($record): string {
                return $record->isPublished() ? 'success' : 'danger';
            })
            ->formatStateUsing(static function () use ($record): string {
                return $record->isPublished()
                    ? __('static_website.public_from_section.public_state_public')
                    : __('static_website.public_from_section.public_state_not_public');
            });

        /** @var Collection<int, string> $snapshotIds */
        $snapshotIds = $record->snapshots()->pluck('id');

        $staticWebsiteSnapshotEntries = $record->getStaticWebsiteSnapshotEntries($snapshotIds);
        if ($staticWebsiteSnapshotEntries->isNotEmpty()) {
            $schema[] = TextEntry::make('id')
                ->label(__('static_website.public_from_section.history'))
                ->formatStateUsing(static function (): string {
                    return '';
                });

            $staticWebsiteHistoryItems = new Collection();

            foreach ($staticWebsiteSnapshotEntries as $staticWebsiteSnapshotEntry) {
                if ($staticWebsiteSnapshotEntry->end_date === null) {
                    $startDate = DateFormatService::toDateTime($staticWebsiteSnapshotEntry->start_date);
                    Assert::notNull($startDate);

                    $staticWebsiteHistoryItems->push(__('static_website.public_from_section.public_history_since', [
                        'start' => $startDate,
                    ]));

                    continue;
                }

                $startDate = DateFormatService::toSentence($staticWebsiteSnapshotEntry->start_date);
                Assert::notNull($startDate);
                $endDate = DateFormatService::toSentence($staticWebsiteSnapshotEntry->end_date);
                Assert::notNull($endDate);

                $staticWebsiteHistoryItems->push(__('static_website.public_from_section.public_history_from_to', [
                    'start' => $startDate,
                    'end' => $endDate,
                ]));
            }

            $schema[] = TextEntry::make('id')
                ->label(__('static_website.public_from_section.public_history'))
                ->view('filament.forms.components.section.static-website-history', ['items' => $staticWebsiteHistoryItems]);
        }

        return $schema;
    }
}
