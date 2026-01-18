<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Filament\Forms\FormHelper;
use Closure;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Webmozart\Assert\Assert;

use function __;
use function array_merge;

class OutsideEuCountryInputGroup extends Group
{
    public static function make(array|Closure $schema = []): static
    {
        $countryOtherTranslation = __('general.country_other');
        Assert::string($countryOtherTranslation);

        $countryOptions = array_merge(__('general.country_options'), [$countryOtherTranslation]);
        Assert::allString($countryOptions);

        return parent::make()
            ->schema([
                Select::make('country')
                    ->label(__('general.country'))
                    ->live()
                    ->options(FormHelper::setValueAsKey($countryOptions))
                    ->in($countryOptions),
                TextInput::make('country_other')
                    ->maxLength(255)
                    ->label($countryOtherTranslation)
                    ->visible(FormHelper::fieldValueEquals(['country' => $countryOtherTranslation]))
                    ->required(FormHelper::fieldValueEquals(['country' => $countryOtherTranslation])),
            ]);
    }
}
