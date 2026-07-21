<?php

declare(strict_types=1);

return [
    // limits for reading uploaded transfer zips
    'max_zipped_number_of_files' => (int) env('TRANSFER_MAX_ZIPPED_NUMBER_OF_FILES', 5_000),
    // in MB, per file in the zip
    'max_zipped_filesize' => (int) env('TRANSFER_MAX_ZIPPED_FILESIZE', 50),
];
