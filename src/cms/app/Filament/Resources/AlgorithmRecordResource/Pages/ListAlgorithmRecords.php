<?php

declare(strict_types=1);

namespace App\Filament\Resources\AlgorithmRecordResource\Pages;

use App\Filament\Actions\ExportAction;
use App\Filament\Exports\AlgorithmRecordExporter;
use App\Filament\Resources\AlgorithmRecordResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use function __;

class ListAlgorithmRecords extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = AlgorithmRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(AlgorithmRecordExporter::class)
                ->pluralModelLabel(__('algorithm_record.model_plural')),
            Actions\CreateAction::make(),
        ];
    }
}
