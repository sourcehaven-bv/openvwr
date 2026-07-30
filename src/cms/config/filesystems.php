<?php

declare(strict_types=1);

use Webmozart\Assert\Assert;

$sharedStoragePath = env('FILESYSTEM_SHARED_STORAGE_PATH', storage_path('app/shared-storage'));
Assert::string($sharedStoragePath);

$staticWebsiteRoot = env('FILESYSTEM_STATIC_WEBSITE_ROOT', storage_path('app/static-website'));
Assert::string($staticWebsiteRoot);

// Object storage is opt-in: set FILESYSTEM_SHARED_DRIVER=s3 to move the shared
// disks (uploads, exports, transfer) to an S3-compatible bucket. Everything
// defaults to 'local', so dev, CI and existing deployments keep working
// untouched. See docs/object_storage.md.
$sharedDriver = env('FILESYSTEM_SHARED_DRIVER', 'local');
Assert::inArray($sharedDriver, ['local', 's3'], 'FILESYSTEM_SHARED_DRIVER must be "local" or "s3", got "%s".');

/**
 * Build a shared disk definition for whichever driver is configured.
 *
 * The two branches are deliberately equivalent in behaviour: `$subPath` is a
 * directory under the shared root on local, and a key prefix inside the bucket
 * on s3, so stored paths are identical either way.
 *
 * @param array<string, mixed> $extra disk settings that do not depend on the driver
 *
 * @return array<string, mixed>
 */
$sharedDisk = static function (
    string $subPath,
    string $bucketEnvKey,
    array $extra = [],
) use (
    $sharedDriver,
    $sharedStoragePath,
): array {
    if ($sharedDriver === 'local') {
        return ['driver' => 'local', 'root' => $sharedStoragePath . '/' . $subPath, ...$extra];
    }

    return [
        'driver' => 's3',
        'bucket' => env($bucketEnvKey, $subPath),
        'endpoint' => env('AWS_ENDPOINT'),
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'eu-central-1'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
        ...$extra,
    ];
};

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => 'app',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        // default disk for (temporary) storage, will not be shared between releases
        'app' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        // used by filament, .e.g export
        'filament' => $sharedDisk('exports', 'EXPORTS_BUCKET', [
            'throw' => false,
            'allowed_mimetypes' => ['xlsx'],
            'max_file_size' => '20mb',
            'visibility' => 'public',
        ]),

        // Transfer bundles: exports awaiting download and uploaded imports being
        // staged. Private, and separate from 'filament' because these zips hold a
        // full register export -- on a public bucket they would be world-readable.
        'transfer' => $sharedDisk('transfer', 'TRANSFER_BUCKET', [
            'throw' => false,
            'visibility' => 'private',
        ]),

        // used for static-website output (see config/static-website.php)
        'static-website' => [
            'driver' => 'local',
            'root' => $staticWebsiteRoot,
            'throw' => false,
        ],

        // used for uploading images (see config/media-library.php)
        'media-library' => $sharedDisk('uploads', 'UPLOADS_BUCKET', [
            'throw' => false,
            'allowed_extensions' => ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'docx', 'odt', 'md', 'txt', 'eml', 'msg'],
            'max_file_size' => '20mb',
            'visibility' => 'private',
        ]),

        // used to store generated sql-migration files (see config/sql-generator.php)
        'sql-generation' => [
            'driver' => 'local',
            'root' => database_path('sql'),
            'throw' => false,
        ],

        // only used to access the .db-requirements file in a docker-setup as volume (see config/sql-generator.php)
        'sql-generation-management' => [
            'driver' => 'local',
            'root' => storage_path('app/sql-generation-management'),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        // see env(STATIC_WEBSITE_BASE_URL) for the path, and config/static_website.php -> static_website_folder for the folder-suffix
        public_path('static-website') => sprintf('%s/static-website', $staticWebsiteRoot),
    ],
];
