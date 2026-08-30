<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResponsibleResource\Pages;

use App\Filament\Actions\CreateSnapshotAction;
use App\Filament\Pages\Concerns\SavesConceptWithoutRequiredFields;
use App\Filament\Resources\ResponsibleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResponsible extends EditRecord
{
    use SavesConceptWithoutRequiredFields;

    protected static string $resource = ResponsibleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateSnapshotAction::makeWithChangesCheck($this->data, $this->savedDataHash),
            DeleteAction::make(),
        ];
    }
}
