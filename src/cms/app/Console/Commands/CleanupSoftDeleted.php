<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Config\Config;
use App\Services\Cleanup\SoftDeleteCleaner;
use Illuminate\Console\Command;

use function array_sum;
use function class_basename;
use function sprintf;

class CleanupSoftDeleted extends Command
{
    protected $signature = 'cleanup:soft-deleted';
    protected $description = 'Permanently delete records that were soft deleted longer ago than the retention period';

    public function handle(SoftDeleteCleaner $cleaner): int
    {
        $retentionDays = Config::integer('cleanup.retention_days');

        $this->info(sprintf('Cleaning up records deleted more than %d days ago...', $retentionDays));

        $deleted = $cleaner->cleanupExpired();

        foreach ($deleted as $modelClass => $count) {
            $this->line(sprintf('%s: %d', class_basename($modelClass), $count));
        }

        $this->info(sprintf('%d records permanently deleted.', array_sum($deleted)));

        return self::SUCCESS;
    }
}
