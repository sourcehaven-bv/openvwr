<?php

declare(strict_types=1);

return [
    'upload_success' => 'Upload succeeded, import started',
    'upload_success_body' => 'The import file was uploaded successfully. The import of the processing activities now starts in the background. A notification will appear for each file whose processing activities have been completed.',
    'started' => 'Import started',
    'finished' => 'Import completed',
    'failed' => 'Import failed',

    'files' => 'File(s)',
    'help' => 'Upload the .zip files here and start the import.',
    'notification' => [
        'body' => 'Import of :model completed (:total_rows rows in total, :successful_rows successful, :failed_rows failed)',
    ],
];
