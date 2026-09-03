<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactPersonResource\Pages;

use App\Filament\Resources\ContactPersonResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContactPersons extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = ContactPersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
