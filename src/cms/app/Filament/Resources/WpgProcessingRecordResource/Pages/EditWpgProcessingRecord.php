<?php

declare(strict_types=1);

namespace App\Filament\Resources\WpgProcessingRecordResource\Pages;

use App\Filament\Actions\CloneAction;
use App\Filament\Actions\CreateSnapshotAction;
use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\ProcessingRecordEditRecord;
use App\Filament\Resources\WpgProcessingRecordResource;
use Filament\Actions\DeleteAction;

class EditWpgProcessingRecord extends ProcessingRecordEditRecord
{
    protected static string $resource = WpgProcessingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
            CloneAction::make(),
            CreateSnapshotAction::makeWithChangesCheck($this->data, $this->savedDataHash),
            DeleteAction::make(),
        ];
    }
}
