<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\CheckboxList as FilamentCheckboxList;
use Illuminate\Validation\Rule;

use function array_keys;

class CheckboxList extends FilamentCheckboxList
{
    /**
     * @param array<string, string> $options
     */
    public static function makeWithValidatedOptions(string $name, array $options): static
    {
        return parent::make($name)
            ->options($options)
            ->rules([
                'array',
                Rule::in(array_keys($options)),
            ]);
    }
}
