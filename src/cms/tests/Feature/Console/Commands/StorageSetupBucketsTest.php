<?php

declare(strict_types=1);

use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\Storage;

/**
 * Swap in a disk that reports itself as s3 and hands back the given client, so the
 * command can be exercised without an actual bucket service.
 */
function fakeS3Disk(S3Client $client): void
{
    config([
        'filesystems.disks.media-library.driver' => 's3',
        'filesystems.disks.media-library.bucket' => 'uploads',
        'filesystems.disks.filament.driver' => 's3',
        'filesystems.disks.filament.bucket' => 'exports',
        'filesystems.disks.transfer.driver' => 's3',
        'filesystems.disks.transfer.bucket' => 'transfer',
    ]);

    $adapter = Mockery::mock(AwsS3V3Adapter::class);
    $adapter->shouldReceive('getClient')->andReturn($client);

    Storage::shouldReceive('disk')->andReturn($adapter);
}

it('does nothing when the shared disks are on the local driver', function (): void {
    config([
        'filesystems.disks.media-library.driver' => 'local',
        'filesystems.disks.filament.driver' => 'local',
        'filesystems.disks.transfer.driver' => 'local',
    ]);

    $this->artisan('storage:setup-buckets')
        ->expectsOutputToContain('Disk media-library is not on object storage, nothing to create.')
        ->expectsOutputToContain('Disk filament is not on object storage, nothing to create.')
        ->expectsOutputToContain('Disk transfer is not on object storage, nothing to create.')
        ->assertSuccessful();
});

it('creates every bucket that does not exist yet', function (): void {
    $client = Mockery::mock(S3Client::class);
    $client->shouldReceive('doesBucketExistV2')->times(3)->andReturnFalse();
    $client->shouldReceive('createBucket')->times(3);

    fakeS3Disk($client);

    $this->artisan('storage:setup-buckets')
        ->expectsOutputToContain('Bucket uploads created successfully.')
        ->expectsOutputToContain('Bucket exports created successfully.')
        ->expectsOutputToContain('Bucket transfer created successfully.')
        ->assertSuccessful();
});

it('leaves buckets that already exist alone', function (): void {
    $client = Mockery::mock(S3Client::class);
    $client->shouldReceive('doesBucketExistV2')->times(3)->andReturnTrue();
    $client->shouldReceive('createBucket')->never();

    fakeS3Disk($client);

    $this->artisan('storage:setup-buckets')
        ->expectsOutputToContain('Bucket uploads already exists.')
        ->assertSuccessful();
});

it('fails when the bucket service reports an error', function (): void {
    $client = Mockery::mock(S3Client::class);
    $client->shouldReceive('doesBucketExistV2')->andThrow(
        new AwsException('connection refused', Mockery::mock(CommandInterface::class)),
    );

    fakeS3Disk($client);

    $this->artisan('storage:setup-buckets')
        ->expectsOutputToContain('Error with bucket uploads')
        ->assertFailed();
});

it('fails when an s3 disk does not expose a client', function (): void {
    config([
        'filesystems.disks.media-library.driver' => 's3',
        'filesystems.disks.media-library.bucket' => 'uploads',
    ]);

    // A disk that is not an AwsS3V3Adapter: the driver says s3, but there is no
    // client behind it, which is the misconfiguration this guards against.
    Storage::shouldReceive('disk')->andReturn(Mockery::mock(Filesystem::class));

    $this->artisan('storage:setup-buckets')
        ->expectsOutputToContain('Disk media-library does not expose an S3 client.')
        ->assertFailed();
});
