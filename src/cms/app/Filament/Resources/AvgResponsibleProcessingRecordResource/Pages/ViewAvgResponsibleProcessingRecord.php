<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages;

use App\Filament\Actions\GoToPublicPageAction;
use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\ProcessingRecordViewRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use Filament\Actions\DeleteAction;

class ViewAvgResponsibleProcessingRecord extends ProcessingRecordViewRecord
{
    protected static string $resource = AvgResponsibleProcessingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
            GoToPublicPageAction::make(),
            DeleteAction::make(),
        ];
    }
}
