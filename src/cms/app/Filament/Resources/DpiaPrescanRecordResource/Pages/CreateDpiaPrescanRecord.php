<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaPrescanRecordResource\Pages;

use App\Filament\Actions\ToggleRegisterLayoutAction;
use App\Filament\Pages\EntityNumberCreateRecord;
use App\Filament\Resources\DpiaPrescanRecordResource;

class CreateDpiaPrescanRecord extends EntityNumberCreateRecord
{
    protected static string $resource = DpiaPrescanRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleRegisterLayoutAction::make(),
        ];
    }
}
