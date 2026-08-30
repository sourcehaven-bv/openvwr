<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemResource\Pages;

use App\Filament\Actions\CreateSnapshotAction;
use App\Filament\Pages\ConceptEditRecord;
use App\Filament\Resources\SystemResource;
use Filament\Actions\DeleteAction;

class EditSystem extends ConceptEditRecord
{
    protected static string $resource = SystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateSnapshotAction::makeWithChangesCheck($this->data, $this->savedDataHash),
            DeleteAction::make(),
        ];
    }
}
