<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Filament\Pages\Contracts\SavesConcepts;
use Filament\Forms\Form;

use function array_filter;
use function array_values;
use function is_string;

/**
 * A form that does not enforce required fields on pages which save concepts.
 *
 * Saving a half-finished record is a deliberate feature: a user may fill in what they
 * know, save, and continue later. Required fields are enforced when a version
 * (snapshot) is created instead, which is the moment the record enters the approval
 * process and must be complete.
 *
 * The `->required()` declarations in the form schemas remain untouched and stay the
 * single source of truth. Only the resulting `required` rule is dropped; every other
 * rule (maxLength, regex, date comparisons, …) still applies, so a concept can never
 * be saved with genuinely invalid data.
 *
 * Whether to drop it is read from the owning page rather than from global state, so
 * there is no flag that can leak across requests.
 */
class DraftableForm extends Form
{
    /**
     * @return array<string, array<mixed>>
     */
    public function getValidationRules(): array
    {
        $rules = parent::getValidationRules();

        if (!$this->getLivewire() instanceof SavesConcepts) {
            return $rules;
        }

        foreach ($rules as $statePath => $componentRules) {
            $rules[$statePath] = array_values(array_filter(
                $componentRules,
                static fn (mixed $rule): bool => !is_string($rule) || $rule !== 'required',
            ));
        }

        return $rules;
    }
}
