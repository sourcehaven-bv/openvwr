<?php

declare(strict_types=1);

namespace App\Filament\Resources\RetentionPeriodResource\Pages;

use App\Filament\Resources\RetentionPeriodResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRetentionPeriod extends ViewRecord
{
    protected static string $resource = RetentionPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
