<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReceiverResource\Pages;

use App\Filament\Actions\CreateSnapshotAction;
use App\Filament\Pages\Concerns\SavesConceptWithoutRequiredFields;
use App\Filament\Resources\ReceiverResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReceiver extends EditRecord
{
    use SavesConceptWithoutRequiredFields;

    protected static string $resource = ReceiverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateSnapshotAction::makeWithChangesCheck($this->data, $this->savedDataHash),
            DeleteAction::make(),
        ];
    }
}
