<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Services\Snapshot\DraftSave;
use Filament\Forms\Form;

use function array_values;
use function is_string;

/**
 * A form that does not enforce required fields while a concept is being saved.
 *
 * Saving a half-finished record is a deliberate feature: a user may fill in what they
 * know, save, and continue later. Required fields are enforced when a version
 * (snapshot) is created instead, which is the moment the record enters the approval
 * process and must be complete.
 *
 * The `->required()` declarations in the form schemas remain untouched and stay the
 * single source of truth. This only drops the resulting `required` rule for the
 * duration of a draft save; every other rule (maxLength, regex, date comparisons, …)
 * still applies, so a concept can never be saved with genuinely invalid data.
 */
class DraftableForm extends Form
{
    /**
     * @return array<string, array<mixed>>
     */
    public function getValidationRules(): array
    {
        $rules = parent::getValidationRules();

        if (! DraftSave::isSavingDraft()) {
            return $rules;
        }

        foreach ($rules as $statePath => $componentRules) {
            $rules[$statePath] = array_values(array_filter(
                $componentRules,
                static fn (mixed $rule): bool => ! is_string($rule) || $rule !== 'required',
            ));
        }

        return $rules;
    }
}
