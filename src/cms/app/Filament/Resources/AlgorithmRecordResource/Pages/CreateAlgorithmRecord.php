<?php

declare(strict_types=1);

namespace App\Filament\Resources\AlgorithmRecordResource\Pages;

use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\EntityNumberCreateRecord;
use App\Filament\Resources\AlgorithmRecordResource;

class CreateAlgorithmRecord extends EntityNumberCreateRecord
{
    protected static string $resource = AlgorithmRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
        ];
    }
}
