<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Config\Config;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Filesystem\AwsS3V3Adapter as LaravelAwsS3V3Adapter;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

use function sprintf;

/**
 * Creates the buckets backing the shared disks.
 *
 * Object storage is opt-in (FILESYSTEM_SHARED_DRIVER), so this is a no-op unless
 * the disks actually run on s3: on a local disk the directories are created on
 * demand and there is nothing to provision. Safe to run repeatedly -- existing
 * buckets are left alone.
 */
#[AsCommand(name: 'storage:setup-buckets', description: 'Create the buckets for the shared object-storage disks')]
class StorageSetupBuckets extends Command
{
    private const array DISKS = ['media-library', 'filament', 'transfer'];

    public function handle(): int
    {
        foreach (self::DISKS as $diskName) {
            if (Config::string(sprintf('filesystems.disks.%s.driver', $diskName)) !== 's3') {
                $this->info(sprintf('Disk %s is not on object storage, nothing to create.', $diskName));

                continue;
            }

            if (!$this->createBucket($diskName)) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    private function createBucket(string $diskName): bool
    {
        $bucket = Config::string(sprintf('filesystems.disks.%s.bucket', $diskName));
        $disk = Storage::disk($diskName);

        if (!$disk instanceof LaravelAwsS3V3Adapter) {
            $this->error(sprintf('Disk %s does not expose an S3 client.', $diskName));

            return false;
        }

        /** @var S3Client $s3Client */
        $s3Client = $disk->getClient();

        try {
            if ($s3Client->doesBucketExistV2($bucket)) {
                $this->info(sprintf('Bucket %s already exists.', $bucket));

                return true;
            }

            $s3Client->createBucket(['Bucket' => $bucket]);
            $this->info(sprintf('Bucket %s created successfully.', $bucket));
        } catch (Throwable $e) {
            $this->error(sprintf('Error with bucket %s: %s', $bucket, $e->getMessage()));

            return false;
        }

        return true;
    }
}
