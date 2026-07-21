<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages;

use App\Filament\Actions\ExportAction;
use App\Filament\Exports\AvgResponsibleProcessingRecordExporter;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use function __;

class ListAvgResponsibleProcessingRecords extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = AvgResponsibleProcessingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(AvgResponsibleProcessingRecordExporter::class)
                ->pluralModelLabel(__('avg_responsible_processing_record.model_plural')),
            CreateAction::make()
                ->modelLabel(__('processing_record.model_singular')),
        ];
    }
}
