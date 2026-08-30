<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Forms\DraftableForm;
use App\Filament\Pages\Contracts\SavesConcepts;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

use function array_key_exists;

/**
 * An edit page whose record may be saved as a concept, even half-finished.
 *
 * The tester feedback this addresses: stepping through the wizard with `->skippable()`
 * never complains, but pressing save suddenly reports required fields — often on steps
 * that were passed long ago. Saving half-finished is an intentional, documented feature
 * (docs/handleiding/02_registers.md), so save no longer enforces required fields.
 * They are enforced when a version (snapshot) is created instead.
 */
abstract class ConceptEditRecord extends EditRecord implements SavesConcepts
{
    protected function makeForm(): Form
    {
        return DraftableForm::make($this);
    }

    /**
     * Keeps a cleared required text field storable as a concept.
     *
     * An emptied text field arrives here as null (ConvertEmptyStringsToNull), but
     * columns such as `name` are NOT NULL, so writing null would fail at the database
     * instead of at validation. For a required text field the empty string is the
     * natural "not filled in yet" value, so null is coerced back to it.
     *
     * Only required text fields are touched: an optional field that is genuinely
     * nullable must keep storing null.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);

        foreach ($this->form->getFlatFields() as $statePath => $field) {
            if (!$this->storesEmptyStringWhenCleared($field)) {
                continue;
            }

            if (array_key_exists($statePath, $data) && $data[$statePath] === null) {
                $data[$statePath] = '';
            }
        }

        return $data;
    }

    /**
     * `isRequired()` stays truthful here: DraftableForm drops the `required` validation
     * rule rather than rewriting the field, so the declaration remains readable.
     */
    private function storesEmptyStringWhenCleared(Field $field): bool
    {
        return ($field instanceof TextInput || $field instanceof Textarea) && $field->isRequired();
    }
}
