<?php

declare(strict_types=1);

use App\Jobs\Media\ComputeContentHash;
use App\Jobs\Media\MarkMediaUploadAsValidated;
use App\Jobs\Media\PruneMetaData;
use App\Jobs\Media\ValidateMimeType;
use App\Vendor\MediaLibrary\Media;
use App\Vendor\MediaLibrary\OrganisationAwarePathGenerator;
use App\Vendor\MediaLibrary\PrivateUrlGenerator;

return [
    'path_generator' => OrganisationAwarePathGenerator::class,
    'url_generator' => PrivateUrlGenerator::class,
    'moves_media_on_update' => true,
    'media_model' => Media::class,
    'filesystem_disk' => 'media-library',

    'permitted_file_types' => [
        'attachment' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

            'text/plain',
            'text/csv',

            'image/png',
            'image/jpeg',
        ],
        'organisation_posters' => [
            'image/png',
            'image/jpeg',
        ],
        'public_website_tree' => [
            'image/png',
            'image/jpeg',
        ],
    ],

    // Explicit per-MIME-type extension allowlist. Prevents disguised uploads such as a
    // batch script renamed to .txt exploiting the broad text/plain MIME type mapping.
    'permitted_file_extensions' => [
        'attachment' => [
            'application/pdf' => ['pdf'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
            'text/plain' => ['txt'],
            'text/csv' => ['csv'],
            'image/png' => ['png'],
            'image/jpeg' => ['jpg', 'jpeg'],
        ],
        'organisation_posters' => [
            'image/png' => ['png'],
            'image/jpeg' => ['jpg', 'jpeg'],
        ],
        'public_website_tree' => [
            'image/png' => ['png'],
            'image/jpeg' => ['jpg', 'jpeg'],
        ],
    ],

    'post_media_upload_job_chain' => [
        PruneMetaData::class,
        ComputeContentHash::class,
        ValidateMimeType::class,
        MarkMediaUploadAsValidated::class,
    ],
];
