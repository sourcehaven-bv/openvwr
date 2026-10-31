<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use App\Config\Config;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;
use InvalidArgumentException;
use Tests\TestCase;

use function config_path;
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

/**
 * Evaluate config/filesystems.php with the given environment, so the s3 branch can
 * be checked without a bucket service. A null value removes the variable.
 *
 * @param array<string, string|null> $env
 *
 * @return array<string, mixed>
 */
function filesystemsConfigWith(array $env): array
{
    // Laravel's own repository is immutable; this one writes to the same places.
    $repository = RepositoryBuilder::createWithDefaultAdapters()->addAdapter(PutenvAdapter::class)->make();
    $previous = [];

    foreach ($env as $name => $value) {
        $previous[$name] = $repository->get($name);
        setTestEnvironmentVariable($repository, $name, $value);
    }

    try {
        return require config_path('filesystems.php');
    } finally {
        foreach ($previous as $name => $value) {
            setTestEnvironmentVariable($repository, $name, $value);
        }
    }
}

function setTestEnvironmentVariable(RepositoryInterface $repository, string $name, ?string $value): void
{
    if ($value === null) {
        $repository->clear($name);

        return;
    }

    $repository->set($name, $value);
}

// POLICY-014: confidential data is stored encrypted at rest (AES-256 at minimum).
it('encrypts objects on the s3 disks by default', function (string $disk): void {
    $config = filesystemsConfigWith(['FILESYSTEM_SHARED_DRIVER' => 's3', 'AWS_SERVER_SIDE_ENCRYPTION' => null]);

    $this->assertSame(['ServerSideEncryption' => 'AES256'], $config['disks'][$disk]['options']);
})->with(['filament', 'media-library', 'transfer']);

it('allows turning server-side encryption off for a development server', function (): void {
    $config = filesystemsConfigWith(['FILESYSTEM_SHARED_DRIVER' => 's3', 'AWS_SERVER_SIDE_ENCRYPTION' => 'none']);

    $this->assertSame([], $config['disks']['media-library']['options']);
});

it('rejects an unknown server-side encryption setting', function (): void {
    filesystemsConfigWith(['FILESYSTEM_SHARED_DRIVER' => 's3', 'AWS_SERVER_SIDE_ENCRYPTION' => 'aes256']);
})->throws(InvalidArgumentException::class, 'AWS_SERVER_SIDE_ENCRYPTION must be "AES256" or "none"');
