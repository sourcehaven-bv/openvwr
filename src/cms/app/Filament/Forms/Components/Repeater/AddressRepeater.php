<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Repeater;

use App\Filament\Resources\AddressResource\AddressResourceForm;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;

use function __;

class AddressRepeater extends Repeater
{
    public static function make(?string $name = 'address'): static
    {
        return parent::make($name)
            ->label(__('address.model_plural'))
            ->relationship()
            ->schema(AddressResourceForm::getSchema())
            ->defaultItems(0)
            ->maxItems(1)
            ->addActionLabel(__('address.add_action_label'))
            ->deleteAction(static function (Action $action): Action {
                return $action->requiresConfirmation();
            });
    }
}
