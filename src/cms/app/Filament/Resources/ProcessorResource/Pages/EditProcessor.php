<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProcessorResource\Pages;

use App\Filament\Actions\CreateSnapshotAction;
use App\Filament\Pages\ConceptEditRecord;
use App\Filament\Resources\ProcessorResource;
use Filament\Actions\DeleteAction;

class EditProcessor extends ConceptEditRecord
{
    protected static string $resource = ProcessorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateSnapshotAction::makeWithChangesCheck($this->data, $this->savedDataHash),
            DeleteAction::make(),
        ];
    }
}
