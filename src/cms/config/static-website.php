<?php

declare(strict_types=1);

return [
    // the default content generator (options: "hugo", "fake")
    'filesystem' => env('STATIC_WEBSITE_FILESYSTEM', 'hugo'),

    // the default content generator (options: "hugo", "fake")
    'static_website_generator' => env('STATIC_WEBSITE_GENERATOR', 'hugo'),

    // the disk to use for the content & website
    'hugo_filesystem_disk' => 'static-website',

    // the folder on the disk, used for static-content
    'hugo_content_folder' => 'static-content',

    // Path to the build script that generates the static website
    // The build script determines where output files are written and which theme to use
    'build_script_path' => env('STATIC_WEBSITE_BUILD_SCRIPT', base_path('static-website/build.sh')),

    // the base url for the static website
    'base_url' => env('STATIC_WEBSITE_BASE_URL'),
    'check_base_url' => env('STATIC_WEBSITE_CHECK_BASE_URL', env('STATIC_WEBSITE_BASE_URL')),
    'check_proxy' => env('STATIC_WEBSITE_CHECK_PROXY'),

    // plan jobs to check deployments after x minutes
    'plan-check-job-delays' => [1, 2, 3, 5, 10],
];
