<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgProcessorProcessingRecordResource\Pages;

use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\EntityNumberCreateRecord;
use App\Filament\Resources\AvgProcessorProcessingRecordResource;

class CreateAvgProcessorProcessingRecord extends EntityNumberCreateRecord
{
    protected static string $resource = AvgProcessorProcessingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
        ];
    }
}
