<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProcessorResource\Pages;

use App\Filament\Resources\ProcessorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProcessor extends ViewRecord
{
    protected static string $resource = ProcessorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
