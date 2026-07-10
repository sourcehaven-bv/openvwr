<?php

declare(strict_types=1);

namespace App\Jobs\Media;

use App\Actions\Media\MediaContentHasher;
use App\Vendor\MediaLibrary\Media;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeContentHash implements ShouldQueue
{
    use Dispatchable;
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use SkipBatchIfCancelledMiddleware;

    public function __construct(
        private readonly Media $media,
    ) {
    }

    public function handle(MediaContentHasher $contentHasher): void
    {
        $this->media->content_hash = $contentHasher->hash($this->media);
        $this->media->saveQuietly();
    }
}
