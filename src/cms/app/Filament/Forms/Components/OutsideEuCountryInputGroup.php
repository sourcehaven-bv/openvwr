<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Filament\Forms\FormHelper;
use App\FixedLists\Lists\AdequacyDecisionCountryList;
use Closure;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\App;
use Webmozart\Assert\Assert;

use function __;
use function array_merge;
use function in_array;

class OutsideEuCountryInputGroup extends Group
{
    public static function make(array|Closure $schema = []): static
    {
        $countryOtherTranslation = __('general.country_other');
        Assert::string($countryOtherTranslation);

        $countryList = App::make(AdequacyDecisionCountryList::class);

        // Retired countries stay selectable in data but are greyed out, so that a record established when the
        // country still had an adequacy decision keeps validating and can be saved again.
        $currentValues = array_merge($countryList->currentValues(), [$countryOtherTranslation]);
        $allValues = array_merge($countryList->allValues(), [$countryOtherTranslation]);

        return parent::make()
            ->schema([
                Select::make('country')
                    ->label(__('general.country'))
                    ->helperText(__('general.help_country'))
                    ->live()
                    ->options(FormHelper::setValueAsKey($allValues))
                    ->disableOptionWhen(static function (string $value) use ($currentValues): bool {
                        return !in_array($value, $currentValues, true);
                    })
                    ->in($allValues),
                TextInput::make('country_other')
                    ->maxLength(255)
                    ->label($countryOtherTranslation)
                    ->visible(FormHelper::fieldValueEquals(['country' => $countryOtherTranslation]))
                    ->required(FormHelper::fieldValueEquals(['country' => $countryOtherTranslation])),
            ]);
    }
}
