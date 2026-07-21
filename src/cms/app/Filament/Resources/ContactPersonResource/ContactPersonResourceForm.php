<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactPersonResource;

use App\Filament\Forms\Components\Repeater\AddressRepeater;
use App\Filament\Forms\Components\Select\SelectSingleWithLookup;
use App\Models\ContactPersonPosition;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

use function __;

class ContactPersonResourceForm
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
            TextInput::make('name')
                ->label(__('general.name'))
                ->required()
                ->maxLength(255),
            SelectSingleWithLookup::makeWithDisabledOptions(
                'contact_person_position_id',
                'contactPersonPosition',
                ContactPersonPosition::class,
                'name',
            )
                ->label(__('contact_person_position.model_singular'))
                ->helperText(__('contact_person.help_position')),
            TextInput::make('email')
                ->label(__('contact_person.email'))
                ->email()
                ->maxLength(255),
            TextInput::make('phone')
                ->label(__('contact_person.phone'))
                ->tel()
                ->maxLength(255),
            AddressRepeater::make()
                ->columnSpan(2),
        ];
    }
}
