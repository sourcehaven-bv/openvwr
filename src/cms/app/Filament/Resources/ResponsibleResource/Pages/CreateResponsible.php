<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResponsibleResource\Pages;

use App\Filament\Pages\ConceptCreateRecord;
use App\Filament\Resources\ResponsibleResource;

class CreateResponsible extends ConceptCreateRecord
{
    protected static string $resource = ResponsibleResource::class;
}
