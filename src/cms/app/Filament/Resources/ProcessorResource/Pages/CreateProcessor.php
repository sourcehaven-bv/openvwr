<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProcessorResource\Pages;

use App\Filament\Pages\ConceptCreateRecord;
use App\Filament\Resources\ProcessorResource;

class CreateProcessor extends ConceptCreateRecord
{
    protected static string $resource = ProcessorResource::class;
}
