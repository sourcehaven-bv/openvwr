<?php

declare(strict_types=1);

use App\Config\Config;
use Aws\S3\S3Client;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\Storage;

function configureBuckets(): array
{
    config()->set('filesystems.disks.media-library.bucket', 'uploads-test-bucket');
    config()->set('filesystems.disks.filament.bucket', 'exports-test-bucket');

    return [
        Config::string('filesystems.disks.media-library.bucket'),
        Config::string('filesystems.disks.filament.bucket'),
    ];
}

function mockS3Disk(string $diskName, object $s3Client): void
{
    $disk = mock(AwsS3V3Adapter::class);
    $disk->expects('getClient')->andReturn($s3Client);

    Storage::shouldReceive('disk')->with($diskName)->once()->andReturn($disk);
}

it('creates new buckets', function (): void {
    [$uploadsBucket, $exportsBucket] = configureBuckets();

    $uploadsClient = mock(S3Client::class);
    $exportsClient = mock(S3Client::class);

    mockS3Disk('media-library', $uploadsClient);
    mockS3Disk('filament', $exportsClient);

    $uploadsClient->expects('doesBucketExistV2')->with($uploadsBucket)->andReturn(false);
    $uploadsClient->expects('createBucket')->with(['Bucket' => $uploadsBucket])->andReturn([]);

    $exportsClient->expects('doesBucketExistV2')->with($exportsBucket)->andReturn(false);
    $exportsClient->expects('createBucket')->with(['Bucket' => $exportsBucket])->andReturn([]);

    $this->artisan('minio:setup')
        ->expectsOutput(sprintf('Bucket %s created successfully.', $uploadsBucket))
        ->expectsOutput(sprintf('Bucket %s created successfully.', $exportsBucket))
        ->assertSuccessful();
});

it('handles existing buckets', function (): void {
    [$uploadsBucket, $exportsBucket] = configureBuckets();

    $uploadsClient = mock(S3Client::class);
    $exportsClient = mock(S3Client::class);

    mockS3Disk('media-library', $uploadsClient);
    mockS3Disk('filament', $exportsClient);

    $uploadsClient->expects('doesBucketExistV2')->with($uploadsBucket)->andReturn(true);
    $exportsClient->expects('doesBucketExistV2')->with($exportsBucket)->andReturn(true);

    $this->artisan('minio:setup')
        ->expectsOutput(sprintf('Bucket %s already exists.', $uploadsBucket))
        ->expectsOutput(sprintf('Bucket %s already exists.', $exportsBucket))
        ->assertSuccessful();
});

it('handles errors', function (): void {
    [$uploadsBucket] = configureBuckets();

    $uploadsClient = mock(S3Client::class);
    mockS3Disk('media-library', $uploadsClient);

    $uploadsClient->expects('doesBucketExistV2')
        ->with($uploadsBucket)
        ->andThrow(new Exception('Connection error'));

    $this->artisan('minio:setup')
        ->expectsOutput(sprintf('Error with bucket %s: Connection error', $uploadsBucket))
        ->assertFailed();
});

it('fails when disk is not S3 compatible', function (): void {
    configureBuckets();

    $nonS3Disk = new stdClass();
    Storage::shouldReceive('disk')->with('media-library')->once()->andReturn($nonS3Disk);

    $this->artisan('minio:setup')
        ->expectsOutput('Disk media-library does not expose an S3 client.')
        ->assertFailed();
});
