<?php

declare(strict_types=1);

use App\Jobs\Media\ComputeContentHash;
use App\Jobs\Media\MarkMediaUploadAsValidated;
use App\Jobs\Media\PruneMetaData;
use App\Jobs\Media\ValidateMimeType;
use App\Vendor\MediaLibrary\Media;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Tests\Feature\Jobs\Media\ThrowExceptionJob;
use Tests\Helpers\ConfigTestHelper;

it('can run the chain', function (): void {
    Bus::fake();
    Event::fakeExcept([MediaHasBeenAddedEvent::class]);

    $media = Media::factory()
        ->create();

    ConfigTestHelper::set('media-library.post_media_upload_job_chain', [
        PruneMetaData::class,
        ComputeContentHash::class,
        ValidateMimeType::class,
        MarkMediaUploadAsValidated::class,
    ]);

    event(new MediaHasBeenAddedEvent($media));

    Bus::assertDispatchedSync(PruneMetaData::class);
    Bus::assertDispatchedSync(ComputeContentHash::class);
    Bus::assertDispatchedSync(ValidateMimeType::class);
    Bus::assertDispatchedSync(MarkMediaUploadAsValidated::class);
});

it('deletes the media when an exception is thrown in the chain', function (): void {
    $uuid = fake()->uuid();
    $media = Media::factory()
        ->create(['uuid' => $uuid]);

    Event::fakeExcept([MediaHasBeenAddedEvent::class]);
    ConfigTestHelper::set('media-library.post_media_upload_job_chain', [
        ThrowExceptionJob::class,
    ]);

    event(new MediaHasBeenAddedEvent($media));

    $mediaCount = Media::query()
        ->where(['uuid' => $uuid])
        ->count();
    expect($mediaCount)
        ->toBe(0);
});
