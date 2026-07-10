<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord\Pages;

use App\Filament\Actions\ExportAction;
use App\Filament\Exports\DataBreachRecordExporter;
use App\Filament\Resources\DataBreachRecordResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use function __;

class ListDataBreachRecords extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = DataBreachRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(DataBreachRecordExporter::class)
                ->pluralModelLabel(__('data_breach_record.model_plural')),
            CreateAction::make()
                ->modelLabel(__('data_breach_record.model_singular')),
        ];
    }
}
