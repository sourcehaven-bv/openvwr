<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemResource\Pages;

use App\Filament\Pages\ConceptCreateRecord;
use App\Filament\Resources\SystemResource;

class CreateSystem extends ConceptCreateRecord
{
    protected static string $resource = SystemResource::class;
}
