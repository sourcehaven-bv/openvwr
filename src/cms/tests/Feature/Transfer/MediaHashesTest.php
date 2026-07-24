<?php

declare(strict_types=1);

use App\Enums\Media\MediaGroup;
use App\Models\Document;
use App\Models\Organisation;
use App\Transfer\Import\MediaHashes;
use Illuminate\Support\Facades\Storage;

it('reports no match when the source media block is not a list', function (): void {
    $document = Document::factory()->for(Organisation::factory())->make();

    expect(app(MediaHashes::class)->match($document, ['media' => 'nope']))->toBeFalse();
});

it('reports no match when a source media item carries no content hash', function (): void {
    $document = Document::factory()->for(Organisation::factory())->make();

    $entity = ['media' => [['file_name' => 'x.txt', 'content_hash' => null]]];

    expect(app(MediaHashes::class)->match($document, $entity))->toBeFalse();
});

it('reports no match when the destination media has no computed hash', function (): void {
    Storage::fake('media-library');

    $document = Document::factory()->for(Organisation::factory())->create();
    $media = $document->addMediaFromString('bytes')
        ->usingFileName('x.txt')
        ->toMediaCollection(MediaGroup::ATTACHMENTS->value);

    // A destination file whose hash was never computed cannot be compared: treat as differing.
    $media->content_hash = null;
    $media->saveQuietly();

    $entity = ['media' => [['file_name' => 'x.txt', 'content_hash' => 'somehash']]];

    expect(app(MediaHashes::class)->match($document->refresh(), $entity))->toBeFalse();
});

it('matches when source and destination hashes are equal regardless of order', function (): void {
    Storage::fake('media-library');

    $document = Document::factory()->for(Organisation::factory())->create();
    $document->addMediaFromString('first')->usingFileName('a.txt')->toMediaCollection(MediaGroup::ATTACHMENTS->value);
    $document->addMediaFromString('second')->usingFileName('b.txt')->toMediaCollection(MediaGroup::ATTACHMENTS->value);

    $document->refresh()->load('media');
    $hashes = $document->media->pluck('content_hash')->all();

    // Same set of hashes, reversed order.
    $entity = [
        'media' => [
            ['file_name' => 'b.txt', 'content_hash' => $hashes[1]],
            ['file_name' => 'a.txt', 'content_hash' => $hashes[0]],
        ]];

    expect(app(MediaHashes::class)->match($document, $entity))->toBeTrue();
});
