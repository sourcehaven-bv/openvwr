<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgProcessorProcessingRecordResource\Pages;

use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\ConceptEntityNumberCreateRecord;
use App\Filament\Resources\AvgProcessorProcessingRecordResource;

class CreateAvgProcessorProcessingRecord extends ConceptEntityNumberCreateRecord
{
    protected static string $resource = AvgProcessorProcessingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
        ];
    }
}
