<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use App\Config\Config;
use Tests\TestCase;

use function it;
use function sprintf;
use function uses;

uses(TestCase::class);

// On s3 a 'public' visibility becomes a public-read object ACL, which would
// expose uploads and exports outside the application's authorisation.
it('keeps the shared disks private', function (string $disk): void {
    $this->assertSame(
        'private',
        Config::string(sprintf('filesystems.disks.%s.visibility', $disk)),
        sprintf('Disk %s must be private.', $disk),
    );
})->with(['filament', 'media-library', 'transfer']);
