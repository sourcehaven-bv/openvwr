<?php

declare(strict_types=1);

namespace App\Jobs\Media;

use App\Services\Media\ExifToolService;
use App\Vendor\MediaLibrary\Media;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function unlink;

class PruneMetaData implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use SkipBatchIfCancelledMiddleware;

    public function __construct(
        private readonly Media $media,
    ) {
    }

    /**
     * @throws ProcessFailedException
     */
    public function handle(
        ExifToolService $exifToolService,
    ): void {
        $disk = Storage::disk($this->media->disk);
        $relativePath = $this->media->getPathRelativeToRoot();

        $tempPath = sys_get_temp_dir() . '/prune_meta_' . Str::uuid() . '_' . $this->media->file_name;

        try {
            file_put_contents($tempPath, $disk->get($relativePath));
            $exifToolService->pruneExifData($tempPath);
            if (!file_exists($tempPath)) {
                throw new RuntimeException('Failed to read temporary file: ' . $tempPath);
            }

            $contents = @file_get_contents($tempPath);
            if ($contents === false) {
                throw new RuntimeException('Failed to read temporary file: ' . $tempPath);
            }
            $disk->put($relativePath, $contents);
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }
}
