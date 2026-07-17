<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationResource\Pages;

use App\Filament\Resources\OrganisationResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganisations extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = OrganisationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
