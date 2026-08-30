<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgResponsibleProcessingRecordResource;

use App\Config\Feature;
use App\Filament\Forms\Components\ProcessingRecordWizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;

use function __;

class AvgResponsibleProcessingRecordResourceForm
{
    public static function stepsForm(Form $form): Form
    {
        return $form
            ->schema([
                ProcessingRecordWizard::make()
                    ->schema([
                        Step::make(__('avg_responsible_processing_record.step_processing_name'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getProcessingName()),
                        Step::make(__('avg_responsible_processing_record.step_responsible'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getResponsible()),
                        Step::make(__('avg_responsible_processing_record.step_processor'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getProcessor()),
                        Step::make(__('avg_responsible_processing_record.step_receiver'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getReceiver()),
                        Step::make(__('avg_responsible_processing_record.step_processing_goal'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getProcessingGoal()),
                        Step::make(__('avg_responsible_processing_record.step_stakeholder_data'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getStakeholder()),
                        Step::make(__('avg_responsible_processing_record.step_decision_making'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getDecisionMaking()),
                        Step::make(__('avg_responsible_processing_record.step_system'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getSystem()),
                        Step::make(__('avg_responsible_processing_record.step_security'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getSecurity()),
                        Step::make(__('avg_responsible_processing_record.step_passthrough'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getPassthrough()),
                        Step::make(__('avg_responsible_processing_record.step_geb_dpia'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getGebDpia()),
                        Step::make(__('avg_responsible_processing_record.step_contact_person'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getContactPerson()),
                        Step::make(__('avg_responsible_processing_record.step_attachments'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getAttachments()),
                        Step::make(__('avg_responsible_processing_record.step_remarks'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getRemarks()),
                        Step::make(__('avg_responsible_processing_record.step_publish'))
                            ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getPublish())
                            ->visible(Feature::publishingEnabled()),
                    ])
                    ->skippable()
                    ->persistStepInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    public static function onePageForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('avg_responsible_processing_record.step_processing_name'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getProcessingName())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_name']),
                Section::make(__('avg_responsible_processing_record.step_responsible'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getResponsible())
                    ->extraAttributes(['data-onepage-section' => 'step_responsible']),
                Section::make(__('avg_responsible_processing_record.step_processor'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getProcessor())
                    ->extraAttributes(['data-onepage-section' => 'step_processor']),
                Section::make(__('avg_responsible_processing_record.step_receiver'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getReceiver())
                    ->extraAttributes(['data-onepage-section' => 'step_receiver']),
                Section::make(__('avg_responsible_processing_record.step_processing_goal'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getProcessingGoal())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_goal']),
                Section::make(__('avg_responsible_processing_record.step_stakeholder_data'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getStakeholder())
                    ->extraAttributes(['data-onepage-section' => 'step_stakeholder_data']),
                Section::make(__('avg_responsible_processing_record.step_decision_making'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getDecisionMaking())
                    ->extraAttributes(['data-onepage-section' => 'step_decision_making']),
                Section::make(__('avg_responsible_processing_record.step_system'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getSystem())
                    ->extraAttributes(['data-onepage-section' => 'step_system']),
                Section::make(__('avg_responsible_processing_record.step_security'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getSecurity())
                    ->extraAttributes(['data-onepage-section' => 'step_security']),
                Section::make(__('avg_responsible_processing_record.step_passthrough'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getPassthrough())
                    ->extraAttributes(['data-onepage-section' => 'step_passthrough']),
                Section::make(__('avg_responsible_processing_record.step_geb_dpia'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getGebDpia())
                    ->extraAttributes(['data-onepage-section' => 'step_geb_dpia']),
                Section::make(__('avg_responsible_processing_record.step_contact_person'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getContactPerson())
                    ->extraAttributes(['data-onepage-section' => 'step_contact_person']),
                Section::make(__('avg_responsible_processing_record.step_attachments'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getAttachments())
                    ->extraAttributes(['data-onepage-section' => 'step_attachments']),
                Section::make(__('avg_responsible_processing_record.step_remarks'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getRemarks())
                    ->extraAttributes(['data-onepage-section' => 'step_remarks']),
                Section::make(__('avg_responsible_processing_record.step_publish'))
                    ->schema(AvgResponsibleProcessingRecordResourceFormSchemas::getPublish())
                    ->extraAttributes(['data-onepage-section' => 'step_publish'])
                    ->visible(Feature::publishingEnabled()),
            ]);
    }
}
