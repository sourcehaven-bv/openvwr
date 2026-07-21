<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgProcessorProcessingRecordResource\Pages;

use App\Filament\Actions\ExportAction;
use App\Filament\Exports\AvgProcessorProcessingRecordExporter;
use App\Filament\Resources\AvgProcessorProcessingRecordResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use function __;

class ListAvgProcessorProcessingRecords extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = AvgProcessorProcessingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(AvgProcessorProcessingRecordExporter::class)
                ->pluralModelLabel(__('avg_processor_processing_record.model_plural')),
            CreateAction::make()
                ->modelLabel(__('processing_record.model_singular')),
        ];
    }
}
