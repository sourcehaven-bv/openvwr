<?php

declare(strict_types=1);

namespace Tests\Unit\Vendor\MediaLibrary;

use App\Vendor\MediaLibrary\Media;

use function expect;
use function it;

it('reconstructs the original filename from name and on-disk extension', function (): void {
    $media = new Media();
    $media->name = 'quarterly-report';
    $media->file_name = '01HXYZABC123DEF456.pdf';

    expect($media->getDownloadFilename())
        ->toBe('01HXYZABC123DEF456.pdf');
});

it('falls back to name when the on-disk file has no extension', function (): void {
    $media = new Media();
    $media->name = 'notes';
    $media->file_name = '01HXYZABC123DEF456';

    expect($media->getDownloadFilename())
        ->toBe('01HXYZABC123DEF456');
});
