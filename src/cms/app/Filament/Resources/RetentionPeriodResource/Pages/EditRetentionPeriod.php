<?php

declare(strict_types=1);

namespace App\Filament\Resources\RetentionPeriodResource\Pages;

use App\Filament\Resources\RetentionPeriodResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRetentionPeriod extends EditRecord
{
    protected static string $resource = RetentionPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
