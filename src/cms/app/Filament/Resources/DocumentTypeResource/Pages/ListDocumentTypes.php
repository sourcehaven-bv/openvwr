<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTypeResource\Pages;

use App\Filament\Resources\DocumentTypeResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentTypes extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = DocumentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
