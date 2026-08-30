<?php

declare(strict_types=1);

namespace App\Filament\Resources\LookupListResource;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use function __;

class LookupListResourceForm
{
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(self::getSchema());
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('general.name'))
                ->required()
                ->maxLength(255),
            Toggle::make('enabled')
                ->label(__('general.enabled'))
                ->default(true),
        ];
    }
}
