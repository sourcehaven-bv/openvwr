<?php

declare(strict_types=1);

namespace App\Filament\Resources\RetentionPeriodResource\Pages;

use App\Filament\Resources\Pages\ListLookupListRecords;
use App\Filament\Resources\RetentionPeriodResource;

class ListRetentionPeriods extends ListLookupListRecords
{
    protected static string $resource = RetentionPeriodResource::class;
}
