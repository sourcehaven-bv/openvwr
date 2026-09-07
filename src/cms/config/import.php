<?php

declare(strict_types=1);

use App\Import\Factories\Avg\AvgProcessorProcessingRecordFactory;
use App\Import\Factories\Avg\AvgResponsibleProcessingRecordFactory;
use App\Import\Factories\DataBreachRecordFactory;
use App\Import\Factories\Wpg\WpgProcessingRecordFactory;
use App\Import\Importers\JsonImporter;
use App\Import\Importers\SpreadsheetImporter;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;

return [
    'date' => [
        'expectedFormats' => [
            'Y-m-d\TH:i:s.v',
            'Y-m-d\TH:i:s',
        ],
        'timezone' => 'Europe/Amsterdam',
    ],

    'factories' => [
        'AVG Verantwoordelijke Verwerkingen' => AvgResponsibleProcessingRecordFactory::class,
        'AVG Verwerker Verwerkingen' => AvgProcessorProcessingRecordFactory::class,
        'WPG Verantwoordelijke Verwerkingen' => WpgProcessingRecordFactory::class,
        'Datalekken' => DataBreachRecordFactory::class,
    ],

    'importers' => [
        'json' => JsonImporter::class,
        'xlsx' => SpreadsheetImporter::class,
        'csv' => SpreadsheetImporter::class,
    ],

    'max_zipped_file_filesize_in_mb' => 10,
    'max_number_of_files_in_zip' => 100,

    /*
     * The guided (Excel/CSV) import keeps a whole sheet in memory while the
     * user reviews it, so it is capped; larger migrations go through the zip
     * route, which is queued per row.
     */
    'mapping' => [
        'max_rows' => 5000,
        // Kilobytes; Livewire's own temporary-upload limit is 12 MB.
        'max_upload_kb' => 12_288,
        'sheet_ttl_minutes' => 240,
        // Tried in order; the first exact match wins. Day-first, as the sources
        // are Dutch. A value matching none of these is reported, not guessed.
        'date_formats' => [
            'Y-m-d\TH:i:s.v',
            'Y-m-d\TH:i:s',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd-m-Y',
            'j-n-Y',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/Y',
            'j/n/Y',
        ],
    ],

    'states_to_skip_import' => [
        'Vervallen',
    ],

    'value_converters' => [
        'boolean_true' => [
            'ja',
            'true',
            'yes',
        ],
        'snapshot_state' => [
            'TerReview' => InReview::class,
            'VaststellingAangevraagd' => Approved::class,
            'Vastgesteld' => Established::class,
        ],
    ],

    'zip' => [
        'max_zipped_filesize_in_mb' => 10,
        'max_zipped_number_of_files' => 100,
    ],
];
