<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResponsibleResource\Pages;

use App\Filament\Resources\ResponsibleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewResponsible extends ViewRecord
{
    protected static string $resource = ResponsibleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
