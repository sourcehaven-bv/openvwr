<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages;

use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\EntityNumberCreateRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;

class CreateAvgResponsibleProcessingRecord extends EntityNumberCreateRecord
{
    protected static string $resource = AvgResponsibleProcessingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
        ];
    }
}
