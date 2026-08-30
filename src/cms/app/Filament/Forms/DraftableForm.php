<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Filament\Pages\Contracts\SavesConcepts;
use Filament\Forms\Form;

use function array_map;
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
 * single source of truth. Only the resulting `required` rule is relaxed; every other
 * rule (maxLength, regex, date comparisons, …) still applies to a value that was
 * filled in, so a concept can never be saved with genuinely invalid data.
 *
 * The rule becomes `nullable` rather than disappearing, which is exactly what Filament
 * emits for a field that is not required (see
 * {@see \Filament\Forms\Components\Concerns\CanBeValidated::getRequiredValidationRule()}).
 * Merely dropping it would leave rules such as the `in` of a lookup select running
 * against the null of an empty field, so an untouched select would still refuse to
 * store a concept. `nullable` makes Laravel skip those rules while the field is empty,
 * which is the whole point: what has not been filled in yet is not yet judged.
 *
 * Whether to relax it is read from the owning page rather than from global state, so
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
            $rules[$statePath] = array_map(
                static fn (mixed $rule): mixed => is_string($rule) && $rule === 'required' ? 'nullable' : $rule,
                $componentRules,
            );
        }

        return $rules;
    }
}
