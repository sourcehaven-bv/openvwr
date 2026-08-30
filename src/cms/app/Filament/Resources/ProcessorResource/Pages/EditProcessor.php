<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProcessorResource\Pages;

use App\Filament\Actions\CreateSnapshotAction;
use App\Filament\Pages\Concerns\SavesConceptWithoutRequiredFields;
use App\Filament\Resources\ProcessorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProcessor extends EditRecord
{
    use SavesConceptWithoutRequiredFields;

    protected static string $resource = ProcessorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateSnapshotAction::makeWithChangesCheck($this->data, $this->savedDataHash),
            DeleteAction::make(),
        ];
    }
}
