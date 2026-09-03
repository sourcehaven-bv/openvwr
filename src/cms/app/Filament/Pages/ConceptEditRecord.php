<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Actions\SubmitForReviewAction;
use App\Filament\Forms\DraftableForm;
use App\Filament\Pages\Concerns\CoercesClearedRequiredFields;
use App\Filament\Pages\Concerns\EnforcesRequiredFieldsWhenSubmitting;
use App\Filament\Pages\Concerns\StoresConceptSnapshot;
use App\Filament\Pages\Contracts\SavesConcepts;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;
use Webmozart\Assert\Assert;

/**
 * An edit page whose record may be saved as a concept, even half-finished.
 *
 * The tester feedback this addresses: stepping through the wizard with `->skippable()`
 * never complains, but pressing save suddenly reports required fields — often on steps
 * that were passed long ago. Saving half-finished is an intentional, documented feature
 * (the "Verwerkingsregisters" topic in App\Manual\Content\ReferenceContent), so save
 * no longer enforces required fields.
 * They are enforced when the concept is sent to review instead.
 */
abstract class ConceptEditRecord extends EditRecord implements SavesConcepts
{
    use CoercesClearedRequiredFields;
    use EnforcesRequiredFieldsWhenSubmitting;
    use StoresConceptSnapshot;

    public const SUBMIT_FOR_REVIEW_EVENT = 'submit-concept-for-review-event';

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

    protected function afterSave(): void
    {
        $this->storeConceptSnapshot();
    }

    /**
     * Submits on behalf of the versions table below the form.
     *
     * That table is its own Livewire component and its concept row offers the same
     * "Start vaststellen" as the header. Submitting has to save this form first, which
     * only this component can do, so the row asks the page rather than carrying a second
     * copy of the action that would drift from the button.
     */
    #[On(self::SUBMIT_FOR_REVIEW_EVENT)]
    public function submitConceptForReview(): void
    {
        $name = SubmitForReviewAction::make()->getName();
        Assert::string($name);

        $this->mountAction($name);
    }
}
