<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResponsibleResource;

use App\Filament\Forms\Components\Repeater\AddressRepeater;
use App\Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

use function __;

class ResponsibleResourceForm
{
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(self::getSchema());
    }

    /**
     * @return array<Component>
     */
    public static function getSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('responsible.name'))
                ->helperText(__('responsible.help_name'))
                ->required()
                ->maxLength(255),
            TagsInput::make(),
            AddressRepeater::make()
                ->columnSpan(2),
        ];
    }
}
