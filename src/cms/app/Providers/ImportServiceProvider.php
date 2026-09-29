<?php

declare(strict_types=1);

namespace App\Providers;

use App\Import\Mapping\ArchiveInspector;
use App\Import\ZipImporter;
use Illuminate\Support\ServiceProvider;

class ImportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->when(ZipImporter::class)
            ->needs('$maxZippedFilesize')
            ->giveConfig('import.zip.max_zipped_filesize_in_mb');
        $this->app->when(ZipImporter::class)
            ->needs('$maxZippedNumberOfFiles')
            ->giveConfig('import.zip.max_zipped_number_of_files');
        $this->app->when(ZipImporter::class)
            ->needs('$factories')
            ->giveConfig('import.factories');
        $this->app->when(ZipImporter::class)
            ->needs('$importers')
            ->giveConfig('import.importers');

        $this->app->when(ArchiveInspector::class)
            ->needs('$factories')
            ->giveConfig('import.factories');
    }
}
