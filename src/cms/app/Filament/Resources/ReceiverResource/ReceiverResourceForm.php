<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReceiverResource;

use App\Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;

use function __;

class ReceiverResourceForm
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getSchema());
    }

    /**
     * @return array<Component>
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
