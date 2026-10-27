<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Config\Feature;
use App\Events\StaticWebsite\BuildEvent;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\PublicWebsiteTree;
use App\Models\StaticWebsiteCheck;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

use function max;

/**
 * Saving a record only rebuilds the site when its public_from is already past.
 * A date in the future needs this command to publish it once it arrives.
 */
class StaticWebsitePublishDue extends Command
{
    // A failing build leaves the latest check behind; bound the window so a
    // broken build is retried for an hour instead of on every run forever.
    private const MAX_LOOKBACK_MINUTES = 60;

    protected $signature = 'static-website:publish-due';
    protected $description = 'Publish the static-website when a "public from" date has passed since the last build';

    public function handle(): int
    {
        if (!Feature::publishingEnabled()) {
            return self::SUCCESS;
        }

        $latestBuildDate = StaticWebsiteCheck::query()->orderBy('build_date', 'desc')->first()?->build_date;
        if ($latestBuildDate === null) {
            $this->output->info('No build has been checked yet; nothing to compare against');

            return self::SUCCESS;
        }

        $now = CarbonImmutable::now();
        $since = max($latestBuildDate, $now->subMinutes(self::MAX_LOOKBACK_MINUTES));

        $isDue = AvgResponsibleProcessingRecord::query()->whereBetween('public_from', [$since, $now])->exists()
            || PublicWebsiteTree::query()->whereBetween('public_from', [$since, $now])->exists();

        if (!$isDue) {
            return self::SUCCESS;
        }

        BuildEvent::dispatch();

        $this->output->success('A "public from" date has passed; static website build dispatched');

        return self::SUCCESS;
    }
}
