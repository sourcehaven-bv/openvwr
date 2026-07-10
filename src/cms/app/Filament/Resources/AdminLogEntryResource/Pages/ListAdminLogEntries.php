<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminLogEntryResource\Pages;

use App\Filament\Resources\AdminLogEntryResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Resources\Pages\ListRecords;

class ListAdminLogEntries extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = AdminLogEntryResource::class;
}
