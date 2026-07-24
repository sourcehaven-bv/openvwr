<?php

declare(strict_types=1);

use App\Enums\Media\MediaGroup;
use App\Models\Document;
use App\Models\Organisation;
use App\Transfer\Import\LibraryMediaResolver;
use Illuminate\Support\Facades\Storage;

it('reads media bytes from the source library by uuid', function (): void {
    Storage::fake('media-library');

    $document = Document::factory()->for(Organisation::factory())->create();
    $media = $document->addMediaFromString('hello world')
        ->usingFileName('note.txt')
        ->toMediaCollection(MediaGroup::ATTACHMENTS->value);

    $contents = app(LibraryMediaResolver::class)->resolve(['uuid' => (string) $media->uuid]);

    expect($contents)->toBe('hello world');
});

it('returns null when the media item has no usable uuid', function (): void {
    expect(app(LibraryMediaResolver::class)->resolve([]))->toBeNull()
        ->and(app(LibraryMediaResolver::class)->resolve(['uuid' => 123]))->toBeNull()
        ->and(app(LibraryMediaResolver::class)->resolve(['uuid' => 'not-a-uuid']))->toBeNull();
});

it('returns null when no media exists for the uuid', function (): void {
    expect(app(LibraryMediaResolver::class)->resolve(['uuid' => '019f9271-a7f0-721e-8715-bf50b642e02b']))->toBeNull();
});
