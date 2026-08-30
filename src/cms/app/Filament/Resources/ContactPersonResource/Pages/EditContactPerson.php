<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactPersonResource\Pages;

use App\Filament\Actions\CreateSnapshotAction;
use App\Filament\Pages\Concerns\SavesConceptWithoutRequiredFields;
use App\Filament\Resources\ContactPersonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContactPerson extends EditRecord
{
    use SavesConceptWithoutRequiredFields;

    protected static string $resource = ContactPersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateSnapshotAction::makeWithChangesCheck($this->data, $this->savedDataHash),
            DeleteAction::make(),
        ];
    }
}
