<?php

declare(strict_types=1);

namespace App\Jobs\StaticWebsite;

use App\Components\Uuid\Uuid;
use App\Enums\Queue;
use App\Enums\SitemapType;
use App\Models\StaticWebsiteCheck;
use App\Models\StaticWebsiteSnapshotEntry;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class StaticWebsiteCheckContentProcessorJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    // Arbitrary key, unique to this job.
    private const LOCK_KEY = 0x5157_5343;

    public function __construct(
        private readonly StaticWebsiteCheck $staticWebsiteCheck,
    ) {
        $this->onQueue(Queue::LOW);
    }

    public function handle(
        LoggerInterface $logger,
    ): void {
        try {
            Assert::keyExists($this->staticWebsiteCheck->content, 'pages');
            Assert::isArray($this->staticWebsiteCheck->content['pages']);
        } catch (InvalidArgumentException) {
            $logger->error('invalid content in static-website-check', ['id' => $this->staticWebsiteCheck->id]);
            return;
        }

        DB::transaction(function () use ($logger): void {
            // Each node runs its own queue against the shared database, so two
            // checks can be processed at the same time. Serialise them: the
            // lookup below and the closing sweep must see each other's writes.
            DB::statement('SELECT pg_advisory_xact_lock(?)', [self::LOCK_KEY]);

            if ($this->isSupersededByNewerCheck()) {
                $logger->info('Static website check superseded by a newer build, skipping', [
                    'id' => $this->staticWebsiteCheck->id,
                ]);

                return;
            }

            $this->processPages($logger);
        });

        $logger->info('Static website check content processor job done', ['id' => $this->staticWebsiteCheck->id]);
    }

    /**
     * An older manifest processed after a newer one would close the entries
     * the newer one opened.
     */
    private function isSupersededByNewerCheck(): bool
    {
        return StaticWebsiteCheck::where('build_date', '>', $this->staticWebsiteCheck->build_date)->exists();
    }

    private function processPages(LoggerInterface $logger): void
    {
        Assert::isArray($this->staticWebsiteCheck->content['pages']);

        foreach ($this->staticWebsiteCheck->content['pages'] as $page) {
            try {
                Assert::isArray($page);
                Assert::keyExists($page, 'type');

                if ($page['type'] !== SitemapType::PROCESSING_RECORD->value) {
                    continue;
                }

                Assert::keyExists($page, 'id');
                Assert::string($page['id']);
                Assert::keyExists($page, 'permalink');
                Assert::string($page['permalink']);

                $snapshotId = Uuid::fromString($page['id']);

                $logger->debug('saving static-website snapshot entry', [
                    'snapshot_id' => $snapshotId,
                    'url' => $page['permalink'],
                ]);

                // The URL is not part of the lookup: a record whose URL changed
                // (renamed organisation slug, moved tree node, new base URL) is
                // still the same publication and keeps its start date.
                $staticWebsiteSnapshotEntry = StaticWebsiteSnapshotEntry::query()->firstOrNew(
                    [
                        'snapshot_id' => $snapshotId,
                        'end_date' => null,
                    ],
                    [
                        'start_date' => $this->staticWebsiteCheck->build_date,
                    ],
                );

                $staticWebsiteSnapshotEntry->url = $page['permalink'];
                $staticWebsiteSnapshotEntry->last_static_website_check_id = $this->staticWebsiteCheck->id;
                $staticWebsiteSnapshotEntry->save();
            } catch (InvalidArgumentException $invalidArgumentException) {
                $logger->debug('saving static-website snapshot entry failed', [
                    'expection' => $invalidArgumentException,
                ]);

                continue;
            }
        }

        StaticWebsiteSnapshotEntry::whereNot('last_static_website_check_id', $this->staticWebsiteCheck->id)
            ->whereNull('end_date')
            ->update([
                'end_date' => CarbonImmutable::now(),
            ]);
    }
}
