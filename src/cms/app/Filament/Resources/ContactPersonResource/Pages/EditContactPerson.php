<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactPersonResource\Pages;

use App\Filament\Actions\SubmitForReviewAction;
use App\Filament\Pages\ConceptEditRecord;
use App\Filament\Resources\ContactPersonResource;
use Filament\Actions\DeleteAction;

class EditContactPerson extends ConceptEditRecord
{
    protected static string $resource = ContactPersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SubmitForReviewAction::make(),
            DeleteAction::make(),
        ];
    }
}
