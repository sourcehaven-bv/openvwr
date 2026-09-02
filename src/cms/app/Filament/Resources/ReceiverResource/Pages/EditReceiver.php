<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReceiverResource\Pages;

use App\Filament\Actions\SubmitForReviewAction;
use App\Filament\Pages\ConceptEditRecord;
use App\Filament\Resources\ReceiverResource;
use Filament\Actions\DeleteAction;

class EditReceiver extends ConceptEditRecord
{
    protected static string $resource = ReceiverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SubmitForReviewAction::make(),
            DeleteAction::make(),
        ];
    }
}
