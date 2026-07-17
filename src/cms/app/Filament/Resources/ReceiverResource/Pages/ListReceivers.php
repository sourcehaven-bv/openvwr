<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReceiverResource\Pages;

use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use App\Filament\Resources\ReceiverResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReceivers extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = ReceiverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
