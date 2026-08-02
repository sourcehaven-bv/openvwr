<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaRecordResource;

use App\Filament\Forms\Components\ProcessingRecordWizard;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;

use function __;

/**
 * The DPIA form in both layouts.
 *
 * The steps follow the 17 paragraphs of the Rijksmodel in order, wrapped by an
 * "Algemeen" step at the front and the process steps at the back. The wizard is
 * skippable on purpose: a DPIA is filled in iteratively and rarely front to
 * back, and paragraaf 17 often sends you back to paragraaf 3.
 *
 * @see DpiaRecordResourceFormSchemas for the fields per paragraph.
 */
class DpiaRecordResourceForm
{
    /**
     * The sections, in order. Keyed by the step key used for the one-page
     * anchors so both layouts cannot drift apart.
     *
     * @return array<string, array<Component>>
     */
    private static function sections(): array
    {
        return [
            'step_general' => DpiaRecordResourceFormSchemas::getGeneral(),
            'step_proposal' => DpiaRecordResourceFormSchemas::getProposal(),
            'step_personal_data' => DpiaRecordResourceAssessmentSchemas::getPersonalData(),
            'step_processing' => DpiaRecordResourceAssessmentSchemas::getProcessing(),
            'step_techniques' => DpiaRecordResourceFormSchemas::getTechniques(),
            'step_purposes' => DpiaRecordResourceFormSchemas::getPurposes(),
            'step_parties' => DpiaRecordResourceFormSchemas::getParties(),
            'step_interests' => DpiaRecordResourceFormSchemas::getInterests(),
            'step_locations' => DpiaRecordResourceFormSchemas::getLocations(),
            'step_legal_framework' => DpiaRecordResourceFormSchemas::getLegalFramework(),
            'step_retention' => DpiaRecordResourceFormSchemas::getRetention(),
            'step_legal_basis' => DpiaRecordResourceFormSchemas::getLegalBasis(),
            'step_special_categories' => DpiaRecordResourceFormSchemas::getSpecialCategories(),
            'step_purpose_limitation' => DpiaRecordResourceFormSchemas::getPurposeLimitation(),
            'step_necessity' => DpiaRecordResourceFormSchemas::getNecessity(),
            'step_rights' => DpiaRecordResourceFormSchemas::getRights(),
            'step_risks' => DpiaRecordResourceAssessmentSchemas::getRisks(),
            'step_measures' => DpiaRecordResourceAssessmentSchemas::getMeasures(),
            'step_consultation' => DpiaRecordResourceAssessmentSchemas::getConsultation(),
            'step_review' => DpiaRecordResourceAssessmentSchemas::getReview(),
            'step_relations' => DpiaRecordResourceFormSchemas::getRelations(),
            'step_attachments' => DpiaRecordResourceFormSchemas::getAttachments(),
            'step_remarks' => DpiaRecordResourceFormSchemas::getRemarks(),
        ];
    }

    public static function stepsForm(Form $form): Form
    {
        $steps = [];

        foreach (self::sections() as $key => $schema) {
            $steps[] = Step::make(__('dpia_record.' . $key))->schema($schema);
        }

        return $form
            ->schema([
                ProcessingRecordWizard::make()
                    ->schema($steps)
                    ->skippable()
                    ->persistStepInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    public static function onePageForm(Form $form): Form
    {
        $sections = [];

        foreach (self::sections() as $key => $schema) {
            $sections[] = Section::make(__('dpia_record.' . $key))
                ->schema($schema)
                ->extraAttributes(['data-onepage-section' => $key]);
        }

        return $form->schema($sections);
    }
}
