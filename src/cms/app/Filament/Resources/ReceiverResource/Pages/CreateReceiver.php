<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReceiverResource\Pages;

use App\Filament\Pages\ConceptCreateRecord;
use App\Filament\Resources\ReceiverResource;

class CreateReceiver extends ConceptCreateRecord
{
    protected static string $resource = ReceiverResource::class;
}
