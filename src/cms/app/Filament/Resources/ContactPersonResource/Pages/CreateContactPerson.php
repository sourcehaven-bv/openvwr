<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactPersonResource\Pages;

use App\Filament\Pages\ConceptCreateRecord;
use App\Filament\Resources\ContactPersonResource;

class CreateContactPerson extends ConceptCreateRecord
{
    protected static string $resource = ContactPersonResource::class;
}
