<?php

declare(strict_types=1);

namespace App\Filament\Resources\RetentionPeriodResource\Pages;

use App\Filament\Pages\CreateRecord;
use App\Filament\Resources\RetentionPeriodResource;

class CreateRetentionPeriod extends CreateRecord
{
    protected static string $resource = RetentionPeriodResource::class;
}
