<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\Media;

use App\Jobs\Media\ValidateMimeType;
use App\Vendor\MediaLibrary\Media;
use Tests\Helpers\ConfigTestHelper;
use Throwable;

use function dispatch_sync;
use function it;

it('throws a Exception when the mime type of a media is not in the permitted list', function (): void {
    $media = Media::factory()->create([
        'file_name' => 'test.png',
        'mime_type' => 'image/png',
        'collection_name' => 'attachments',
    ]);

    ConfigTestHelper::set('media-library.permitted_file_types.attachment', ['image/jpg']);

    $job = new ValidateMimeType($media);
    $this->expectException(Throwable::class);
    $this->expectExceptionMessage('Mime type is not permitted');

    dispatch_sync($job);
});

it('does not throw an RunTimeException when the mime type of a media is in the permitted list', function (): void {
    $media = Media::factory()->create([
        'file_name' => 'test.png',
        'mime_type' => 'image/png',
    ]);

    $job = new ValidateMimeType($media);
    dispatch_sync($job);
})->throwsNoExceptions();

it('uses collection-specific permitted mime types when configured', function (): void {
    $media = Media::factory()->create([
        'file_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'collection_name' => 'organisation_posters',
    ]);

    // PDF is in attachment types but not in organisation_posters types
    $job = new ValidateMimeType($media);
    $this->expectException(Throwable::class);
    $this->expectExceptionMessage('Mime type is not permitted');

    dispatch_sync($job);
});

it('allows image mime types for organisation_posters collection', function (): void {
    $media = Media::factory()->create([
        'file_name' => 'poster.jpeg',
        'mime_type' => 'image/jpeg',
        'collection_name' => 'organisation_posters',
    ]);

    $job = new ValidateMimeType($media);
    dispatch_sync($job);
})->throwsNoExceptions();

it('falls back to attachment permitted types for unknown collections', function (): void {
    $media = Media::factory()->create([
        'file_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'collection_name' => 'attachments',
    ]);

    $job = new ValidateMimeType($media);
    dispatch_sync($job);
})->throwsNoExceptions();

it('throws when file extension is not in the permitted list for its mime type', function (): void {
    $media = Media::factory()->create([
        'file_name' => 'malware.bat',
        'mime_type' => 'text/plain',
        'collection_name' => 'attachments',
    ]);

    $job = new ValidateMimeType($media);
    $this->expectException(Throwable::class);
    $this->expectExceptionMessage('File extension is not permitted for this mime type');

    dispatch_sync($job);
});

it('allows a plain text file with a permitted .txt extension', function (): void {
    $media = Media::factory()->create([
        'file_name' => 'readme.txt',
        'mime_type' => 'text/plain',
        'collection_name' => 'attachments',
    ]);

    $job = new ValidateMimeType($media);
    dispatch_sync($job);
})->throwsNoExceptions();

it('allows a file when no extensions are configured for its mime type', function (): void {
    $media = Media::factory()->create([
        'file_name' => 'test.unknown',
        'mime_type' => 'application/x-custom',
        'collection_name' => 'attachments',
    ]);

    ConfigTestHelper::set('media-library.permitted_file_types.attachment', ['application/x-custom']);
    ConfigTestHelper::set('media-library.permitted_file_extensions.attachment', []);

    $job = new ValidateMimeType($media);
    dispatch_sync($job);
})->throwsNoExceptions();

it('does not validate mime type if the batch is cancelled', function (): void {
    $validateMimeType = (new ValidateMimeType(Media::factory()->create()))->withFakeBatch();
    $job = $validateMimeType[0];
    $batch = $validateMimeType[1];

    $batch->cancel();

    dispatch_sync($job);

    $this->assertTrue($batch->cancelled());
    $this->assertEmpty($batch->added);
});
