<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;

use function __;

class AddressResourceForm
{
    /**
     * @return array<Component>
     */
    public static function getSchema(): array
    {
        return [
            Section::make(__('address.section_address'))
                ->columns()
                ->schema([
                    TextInput::make('address')
                        ->label(__('address.address'))
                        ->maxLength(255),
                    TextInput::make('postal_code')
                        ->label(__('address.postal_code'))
                        ->maxLength(255),
                    TextInput::make('city')
                        ->label(__('address.city'))
                        ->maxLength(255),
                    TextInput::make('country')
                        ->label(__('address.country'))
                        ->maxLength(255),
                ]),

            Section::make(__('address.section_postbox'))
                ->columns()
                ->schema([
                    TextInput::make('postbox')
                        ->label(__('address.postbox'))
                        ->maxLength(255),
                    TextInput::make('postbox_postal_code')
                        ->label(__('address.postbox_postal_code'))
                        ->maxLength(255),
                    TextInput::make('postbox_city')
                        ->label(__('address.postbox_city'))
                        ->maxLength(255),
                    TextInput::make('postbox_country')
                        ->label(__('address.postbox_country'))
                        ->maxLength(255),
                ]),
        ];
    }
}
