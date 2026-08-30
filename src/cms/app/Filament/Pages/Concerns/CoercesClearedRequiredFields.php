<?php

declare(strict_types=1);

namespace App\Filament\Pages\Concerns;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

use function array_key_exists;

/**
 * Keeps a cleared required text field storable as a concept.
 *
 * An emptied text field arrives as null (ConvertEmptyStringsToNull), but columns such
 * as `name` are NOT NULL, so writing null would fail at the database instead of at
 * validation. For a required text field the empty string is the natural "not filled in
 * yet" value, so null is coerced back to it.
 *
 * Only required text fields are touched: an optional field that is genuinely nullable
 * must keep storing null.
 *
 * Shared by the concept edit and create pages, which coerce at their own respective
 * hooks ({@see \App\Filament\Pages\ConceptEditRecord} and
 * {@see \App\Filament\Pages\ConceptCreateRecord}).
 */
trait CoercesClearedRequiredFields
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    final protected function coerceClearedRequiredFields(Form $form, array $data): array
    {
        foreach ($form->getFlatFields() as $statePath => $field) {
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
    final protected function storesEmptyStringWhenCleared(Field $field): bool
    {
        return ($field instanceof TextInput || $field instanceof Textarea) && $field->isRequired();
    }
}
