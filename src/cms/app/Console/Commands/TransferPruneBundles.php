<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Filament\Pages\TransferImport;
use App\Transfer\Export\BundleExporter;
use App\Transfer\TransferBundleStorage;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

use function sprintf;

/**
 * Bundles can hold personal data, so they are kept no longer than needed. Exports
 * are only reachable through a download link that expires after the retention
 * period, and imports are only needed while the import runs.
 */
class TransferPruneBundles extends Command
{
    private const array DIRECTORIES = [
        BundleExporter::EXPORT_DIRECTORY,
        TransferImport::IMPORT_DIRECTORY,
    ];

    protected $signature = 'transfer:prune-bundles';
    protected $description = 'Delete transfer bundles older than the retention period.';

    public function handle(TransferBundleStorage $bundleStorage): int
    {
        $disk = $bundleStorage->disk();
        $cutoff = CarbonImmutable::now()->subDays(TransferBundleStorage::RETENTION_DAYS)->getTimestamp();
        $count = 0;

        foreach (self::DIRECTORIES as $directory) {
            foreach ($disk->allFiles($directory) as $path) {
                if ($disk->lastModified($path) >= $cutoff) {
                    continue;
                }

                $disk->delete($path);
                $count++;
            }
        }

        $this->info(sprintf('%d expired transfer bundles deleted.', $count));

        return self::SUCCESS;
    }
}
