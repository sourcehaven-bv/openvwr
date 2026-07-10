<?php

declare(strict_types=1);

namespace App\Filament\Resources\WpgProcessingRecordResource\Pages;

use App\Filament\Actions\ExportAction;
use App\Filament\Exports\WpgProcessingRecordExporter;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use App\Filament\Resources\WpgProcessingRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use function __;

class ListWpgProcessingRecords extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = WpgProcessingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(WpgProcessingRecordExporter::class)
                ->pluralModelLabel(__('wpg_processing_record.model_plural')),
            Actions\CreateAction::make()
                ->modelLabel(__('processing_record.model_singular')),
        ];
    }
}
