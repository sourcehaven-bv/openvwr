<?php

declare(strict_types=1);

use App\Actions\Media\MediaContentHasher;
use App\Jobs\Media\ComputeContentHash;
use App\Vendor\MediaLibrary\Media;

it('sets the content hash on the media model', function (): void {
    $this->mock(MediaContentHasher::class)
        ->shouldReceive('hash')
        ->once()
        ->andReturn('content-hash');

    $media = Media::factory()->create([
        'content_hash' => null,
    ]);

    expect($media->content_hash)->toBeNull();

    (new ComputeContentHash($media))->handle(app(MediaContentHasher::class));

    expect($media->refresh()->content_hash)->toBe('content-hash');
});
