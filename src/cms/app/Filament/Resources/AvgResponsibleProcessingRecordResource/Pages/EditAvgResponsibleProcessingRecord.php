<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages;

use App\Filament\Actions\CloneAction;
use App\Filament\Actions\CreateSnapshotAction;
use App\Filament\Actions\GoToPublicPageAction;
use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\ProcessingRecordEditRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use Filament\Actions\DeleteAction;

class EditAvgResponsibleProcessingRecord extends ProcessingRecordEditRecord
{
    protected static string $resource = AvgResponsibleProcessingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
            GoToPublicPageAction::make(),
            CloneAction::make(),
            CreateSnapshotAction::makeWithChangesCheck($this->data, $this->savedDataHash),
            DeleteAction::make(),
        ];
    }
}
