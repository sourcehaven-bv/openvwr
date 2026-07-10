<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResponsibleLegalEntityResource\Pages;

use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use App\Filament\Resources\ResponsibleLegalEntityResource;
use Filament\Resources\Pages\ListRecords;

class ListResponsibleLegalEnties extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = ResponsibleLegalEntityResource::class;
}
