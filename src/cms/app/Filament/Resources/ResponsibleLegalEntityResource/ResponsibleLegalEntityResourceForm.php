<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResponsibleLegalEntityResource;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

use function __;

class ResponsibleLegalEntityResourceForm
{
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('responsible_legal_entity.name'))
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
