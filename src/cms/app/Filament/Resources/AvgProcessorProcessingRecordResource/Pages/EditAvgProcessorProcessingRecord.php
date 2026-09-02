<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgProcessorProcessingRecordResource\Pages;

use App\Filament\Actions\CloneAction;
use App\Filament\Actions\SubmitForReviewAction;
use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\ProcessingRecordEditRecord;
use App\Filament\Resources\AvgProcessorProcessingRecordResource;
use Filament\Actions\DeleteAction;

class EditAvgProcessorProcessingRecord extends ProcessingRecordEditRecord
{
    protected static string $resource = AvgProcessorProcessingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SubmitForReviewAction::make(),
            ToggleRegisterLayoutAction::make(),
            CloneAction::make(),
            DeleteAction::make(),
        ];
    }
}
