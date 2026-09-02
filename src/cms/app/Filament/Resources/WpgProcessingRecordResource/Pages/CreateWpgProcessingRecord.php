<?php

declare(strict_types=1);

namespace App\Filament\Resources\WpgProcessingRecordResource\Pages;

use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\ConceptEntityNumberCreateRecord;
use App\Filament\Resources\WpgProcessingRecordResource;

class CreateWpgProcessingRecord extends ConceptEntityNumberCreateRecord
{
    protected static string $resource = WpgProcessingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
        ];
    }
}
