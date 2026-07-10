<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\Media;

use App\Jobs\Media\PruneMetaData;
use App\Services\Media\ExifToolService;
use App\Vendor\MediaLibrary\Media;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;

use function chmod;
use function dispatch_sync;
use function expect;
use function it;
use function posix_geteuid;
use function unlink;

it('does not prune metadata if the batch is cancelled', function (): void {
    $this->mock(ExifToolService::class)
        ->expects('pruneExifData')
        ->never();

    $media = Media::factory()
        ->create();

    $pruneMetaData = (new PruneMetaData($media))->withFakeBatch();
    $job = $pruneMetaData[0];
    $batch = $pruneMetaData[1];

    $batch->cancel();

    dispatch_sync($job);
});

it('calls the ExifToolService to prune metadata', function (): void {
    Storage::fake('media-library');

    $media = Media::factory()->create(['mime_type' => 'text/plain']);
    Storage::disk('media-library')->put($media->getPathRelativeToRoot(), 'test content');

    $pruneMetaData = (new PruneMetaData($media))->withFakeBatch();
    $job = $pruneMetaData[0];

    $exifToolService = $this->mock(ExifToolService::class);
    $exifToolService->expects('pruneExifData')
        ->with(Mockery::type('string'));

    $job->handle($exifToolService);
});

it('throws a RuntimeException when the temporary file cannot be read after pruning', function (): void {
    Storage::fake('media-library');

    $media = Media::factory()->create(['mime_type' => 'text/plain']);
    Storage::disk('media-library')->put($media->getPathRelativeToRoot(), 'test content');

    $pruneMetaData = (new PruneMetaData($media))->withFakeBatch();
    $job = $pruneMetaData[0];

    $exifToolService = $this->mock(ExifToolService::class);
    $exifToolService->expects('pruneExifData')
        ->andReturnUsing(function (string $path): void {
            unlink($path);
        });

    expect(fn () => $job->handle($exifToolService))
        ->toThrow(RuntimeException::class, 'Failed to read temporary file');
});

it('throws a RuntimeException when the temporary file cannot be read after pruning due to permission denial', function (): void {
    if (posix_geteuid() === 0) {
        $this->markTestSkipped('Cannot restrict file permissions as root');
    }

    Storage::fake('media-library');

    $media = Media::factory()->create(['mime_type' => 'text/plain']);
    Storage::disk('media-library')->put($media->getPathRelativeToRoot(), 'test content');

    $pruneMetaData = (new PruneMetaData($media))->withFakeBatch();
    $job = $pruneMetaData[0];

    $exifToolService = $this->mock(ExifToolService::class);
    $exifToolService->expects('pruneExifData')
        ->andReturnUsing(function (string $path): void {
            chmod($path, 0000);
        });

    expect(fn () => $job->handle($exifToolService))
        ->toThrow(RuntimeException::class, 'Failed to read temporary file');
});
