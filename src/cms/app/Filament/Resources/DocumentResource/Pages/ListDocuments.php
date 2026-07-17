<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
