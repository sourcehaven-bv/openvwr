<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReceiverResource;

use Filament\Schemas\Schema;
use App\Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;

use function __;

class ReceiverResourceForm
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
            Textarea::make('description')
                ->label(__('receiver.description'))
                ->helperText(__('receiver.help_description'))
                ->required()
                ->maxLength(255),
            TagsInput::make(),
        ];
    }
}
