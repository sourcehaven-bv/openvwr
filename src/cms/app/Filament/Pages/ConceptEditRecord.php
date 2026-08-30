<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Forms\DraftableForm;
use App\Filament\Pages\Concerns\CoercesClearedRequiredFields;
use App\Filament\Pages\Contracts\SavesConcepts;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

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
    use CoercesClearedRequiredFields;

    protected function makeForm(): Form
    {
        return DraftableForm::make($this);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->coerceClearedRequiredFields($this->form, parent::mutateFormDataBeforeSave($data));
    }
}
