<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Forms\DraftableForm;
use App\Filament\Pages\Concerns\CoercesClearedRequiredFields;
use App\Filament\Pages\Contracts\SavesConcepts;
use Filament\Forms\Form;

/**
 * An {@see EntityNumberCreateRecord} whose record may be stored as a concept.
 *
 * The registers keep their entity number generation, which is why this cannot simply
 * extend {@see ConceptCreateRecord}. {@see CreateDpiaPrescanRecord} keeps extending the
 * strict {@see EntityNumberCreateRecord}, because the prescan has no concept editing
 * either.
 *
 * @see ConceptCreateRecord for why the shared base classes are not relaxed wholesale.
 */
abstract class ConceptEntityNumberCreateRecord extends EntityNumberCreateRecord implements SavesConcepts
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
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->coerceClearedRequiredFields($this->form, parent::mutateFormDataBeforeCreate($data));
    }
}
