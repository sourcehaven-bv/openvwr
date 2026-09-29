<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Forms\DraftableForm;
use App\Filament\Pages\Concerns\CoercesClearedRequiredFields;
use App\Filament\Pages\Concerns\EnforcesRequiredFieldsWhenSubmitting;
use App\Filament\Pages\Concerns\StoresConceptSnapshot;
use App\Filament\Pages\Contracts\SavesConcepts;
use Filament\Schemas\Schema;

/**
 * A create page whose record may be stored as a concept, even half-finished.
 *
 * The counterpart of {@see ConceptEditRecord}. Creating was initially left strict on
 * the reasoning that a record needs a name to exist, but a user who starts a record
 * and only knows part of the answers has the same problem there as on edit: the wizard
 * is skippable, yet the final button refuses. So creating no longer enforces required
 * fields either; they are enforced when the concept is sent to review.
 *
 * Only the record types that already have concept editing use this base class. The
 * shared {@see CreateRecord} base is deliberately left alone, because plain lookup
 * lists and administrative resources extend it too and should stay strict.
 */
abstract class ConceptCreateRecord extends CreateRecord implements SavesConcepts
{
    use CoercesClearedRequiredFields;
    use EnforcesRequiredFieldsWhenSubmitting;
    use StoresConceptSnapshot;

    protected function makeSchema(): Schema
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

    protected function afterCreate(): void
    {
        $this->storeConceptSnapshot();
    }
}
