<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\DocumentNotificationsSend;
use App\Console\Commands\SnapshotApprovalBatchNotifications;
use App\Console\Commands\StaticWebsitePublishDue;
use App\Console\Commands\StaticWebsiteRefresh;
use App\Console\Commands\TransferPruneBundles;
use App\Console\Commands\UserDeleteExpiredLoginTokens;
use App\Console\Commands\UserDeleteWithoutOrganisation;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use function sprintf;

class Kernel extends ConsoleKernel
{
    protected function commands(): void
    {
        $this->load(sprintf('%s/Commands', __DIR__));
    }

    protected function schedule(Schedule $schedule): void
    {
        // every fifteen minutes
        $schedule->command(UserDeleteExpiredLoginTokens::class)
            ->everyFifteenMinutes();

        // every five minutes
        $schedule->command(StaticWebsitePublishDue::class)
            ->everyFiveMinutes()
            ->withoutOverlapping();

        // daily
        // 01:30 UTC, clear of the nightly reboots (primaries 23:00, standbys
        // 01:00) that move the database primary and with it the scheduler.
        $schedule->command(StaticWebsiteRefresh::class)
            ->dailyAt('01:30');
        $schedule->command(DocumentNotificationsSend::class)
            ->dailyAt('09:00');
        $schedule->command(UserDeleteWithoutOrganisation::class)
            ->daily();
        $schedule->command(TransferPruneBundles::class)
            ->dailyAt('02:00');

        // weekly
        $schedule->command(SnapshotApprovalBatchNotifications::class)
            ->weeklyOn(Schedule::MONDAY, '09:00');
    }
}
