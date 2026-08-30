<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProcessorResource;

use Filament\Schemas\Schema;
use App\Filament\Forms\Components\Repeater\AddressRepeater;
use App\Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;

use function __;

class ProcessorResourceForm
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
                ->label(__('processor.name'))
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label(__('processor.email'))
                ->email()
                ->required()
                ->maxLength(255),
            TextInput::make('phone')
                ->label(__('processor.phone'))
                ->tel()
                ->required()
                ->maxLength(255),
            TagsInput::make(),
            AddressRepeater::make()
                ->columnSpan(2),
        ];
    }
}
