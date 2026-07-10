<?php

declare(strict_types=1);

namespace App\Jobs\Media;

use App\Config\Config;
use App\Vendor\MediaLibrary\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;
use Throwable;

readonly class PostMediaUploadJobChain
{
    public function run(
        Media $media,
    ): void {
        /** @var array<class-string<ShouldQueue>> $jobClasses */
        $jobClasses = Config::array('media-library.post_media_upload_job_chain');

        try {
            foreach ($jobClasses as $jobClass) {
                Bus::dispatchSync(new $jobClass($media));
            }
        } catch (Throwable) {
            $media->delete();
        }
    }
}
