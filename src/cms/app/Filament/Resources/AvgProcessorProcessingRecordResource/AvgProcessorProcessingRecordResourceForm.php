<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgProcessorProcessingRecordResource;

use App\Filament\Forms\Components\ProcessingRecordWizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;

use function __;

class AvgProcessorProcessingRecordResourceForm
{
    public static function stepsForm(Form $form): Form
    {
        return $form
            ->schema([
                ProcessingRecordWizard::make()
                    ->schema([
                        Step::make(__('avg_processor_processing_record.step_processing_name'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getProcessingName()),
                        Step::make(__('avg_processor_processing_record.step_responsible'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getResponsible()),
                        Step::make(__('avg_processor_processing_record.step_processor'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getProcessors()),
                        Step::make(__('avg_processor_processing_record.step_receiver'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getReceiver()),
                        Step::make(__('avg_processor_processing_record.step_processing_goal'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getProcessingGoal()),
                        Step::make(__('avg_processor_processing_record.step_involved_data'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getInvolvedData()),
                        Step::make(__('avg_processor_processing_record.step_decision_making'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getDecisionMaking()),
                        Step::make(__('avg_processor_processing_record.step_system'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getSystem()),
                        Step::make(__('avg_processor_processing_record.step_security'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getSecurity()),
                        Step::make(__('avg_processor_processing_record.step_passthrough'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getPassthrough()),
                        Step::make(__('avg_processor_processing_record.step_geb_pia'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getGebPia()),
                        Step::make(__('avg_processor_processing_record.step_contact_person'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getContactPerson()),
                        Step::make(__('avg_processor_processing_record.step_attachments'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getAttachments()),
                        Step::make(__('avg_processor_processing_record.step_remarks'))
                            ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getRemarks()),
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
                Section::make(__('avg_processor_processing_record.step_processing_name'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getProcessingName())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_name']),
                Section::make(__('avg_processor_processing_record.step_responsible'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getResponsible())
                    ->extraAttributes(['data-onepage-section' => 'step_responsible']),
                Section::make(__('avg_processor_processing_record.step_processor'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getProcessors())
                    ->extraAttributes(['data-onepage-section' => 'step_processor']),
                Section::make(__('avg_processor_processing_record.step_receiver'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getReceiver())
                    ->extraAttributes(['data-onepage-section' => 'step_receiver']),
                Section::make(__('avg_processor_processing_record.step_processing_goal'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getProcessingGoal())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_goal']),
                Section::make(__('avg_processor_processing_record.step_involved_data'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getInvolvedData())
                    ->extraAttributes(['data-onepage-section' => 'step_involved_data']),
                Section::make(__('avg_processor_processing_record.step_decision_making'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getDecisionMaking())
                    ->extraAttributes(['data-onepage-section' => 'step_decision_making']),
                Section::make(__('avg_processor_processing_record.step_system'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getSystem())
                    ->extraAttributes(['data-onepage-section' => 'step_system']),
                Section::make(__('avg_processor_processing_record.step_security'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getSecurity())
                    ->extraAttributes(['data-onepage-section' => 'step_security']),
                Section::make(__('avg_processor_processing_record.step_passthrough'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getPassthrough())
                    ->extraAttributes(['data-onepage-section' => 'step_passthrough']),
                Section::make(__('avg_processor_processing_record.step_geb_pia'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getGebPia())
                    ->extraAttributes(['data-onepage-section' => 'step_geb_pia']),
                Section::make(__('avg_processor_processing_record.step_contact_person'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getContactPerson())
                    ->extraAttributes(['data-onepage-section' => 'step_contact_person']),
                Section::make(__('avg_processor_processing_record.step_attachments'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getAttachments())
                    ->extraAttributes(['data-onepage-section' => 'step_attachments']),
                Section::make(__('avg_processor_processing_record.step_remarks'))
                    ->schema(AvgProcessorProcessingRecordResourceFormSchemas::getRemarks())
                    ->extraAttributes(['data-onepage-section' => 'step_remarks']),
            ]);
    }
}
