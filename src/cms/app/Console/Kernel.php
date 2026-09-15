<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\CleanupSoftDeleted;
use App\Console\Commands\DocumentNotificationsSend;
use App\Console\Commands\SnapshotApprovalBatchNotifications;
use App\Console\Commands\StaticWebsiteRefresh;
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

        // daily
        $schedule->command(StaticWebsiteRefresh::class)
            ->dailyAt('01:00');
        $schedule->command(DocumentNotificationsSend::class)
            ->dailyAt('09:00');
        $schedule->command(UserDeleteWithoutOrganisation::class)
            ->daily();
        // Ruimt verwijderde gegevens definitief op na de bewaartermijn.
        // 's nachts, want een grote achterstand kan even duren.
        $schedule->command(CleanupSoftDeleted::class)
            ->dailyAt('03:00');

        // weekly
        $schedule->command(SnapshotApprovalBatchNotifications::class)
            ->weeklyOn(Schedule::MONDAY, '09:00');
    }
}
