<?php

declare(strict_types=1);

namespace App\Filament\Resources\WpgProcessingRecordResource;

use App\Filament\Forms\Components\ProcessingRecordWizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;

use function __;

class WpgProcessingRecordResourceForm
{
    public static function stepsForm(Form $form): Form
    {
        return $form
            ->schema([
                ProcessingRecordWizard::make()
                    ->schema([
                        Step::make(__('wpg_processing_record.step_processing_name'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getProcessingName()),
                        Step::make(__('wpg_processing_record.step_responsible'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getResponsible()),
                        Step::make(__('wpg_processing_record.step_processor'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getProcessor()),
                        Step::make(__('wpg_processing_record.step_receiver'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getReceiver()),
                        Step::make(__('wpg_processing_record.step_wpg_goal'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getProcessingGoal()),
                        Step::make(__('wpg_processing_record.step_special_police_data'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getSpecialPoliceData()),
                        Step::make(__('wpg_processing_record.step_decision_making'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getDecisionMaking()),
                        Step::make(__('wpg_processing_record.step_system_application'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getSystems()),
                        Step::make(__('wpg_processing_record.step_security'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getSecurity()),
                        Step::make(__('wpg_processing_record.step_geb_dpia'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getGebDpia()),
                        Step::make(__('wpg_processing_record.step_contact_person'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getContactPersons()),
                        Step::make(__('wpg_processing_record.step_attachments'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getAttachments()),
                        Step::make(__('wpg_processing_record.step_remarks'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getRemarks()),
                        Step::make(__('wpg_processing_record.step_categories_involved'))
                            ->schema(WpgProcessingRecordResourceFormSchemas::getCategoriesInvolved()),
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
                Section::make(__('wpg_processing_record.step_processing_name'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getProcessingName())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_name']),
                Section::make(__('wpg_processing_record.step_responsible'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getResponsible())
                    ->extraAttributes(['data-onepage-section' => 'step_responsible']),
                Section::make(__('wpg_processing_record.step_processor'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getProcessor())
                    ->extraAttributes(['data-onepage-section' => 'step_processor']),
                Section::make(__('wpg_processing_record.step_receiver'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getReceiver())
                    ->extraAttributes(['data-onepage-section' => 'step_receiver']),
                Section::make(__('wpg_processing_record.step_wpg_goal'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getProcessingGoal())
                    ->extraAttributes(['data-onepage-section' => 'step_wpg_goal']),
                Section::make(__('wpg_processing_record.step_special_police_data'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getSpecialPoliceData())
                    ->extraAttributes(['data-onepage-section' => 'step_special_police_data']),
                Section::make(__('wpg_processing_record.step_decision_making'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getDecisionMaking())
                    ->extraAttributes(['data-onepage-section' => 'step_decision_making']),
                Section::make(__('wpg_processing_record.step_system_application'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getSystems())
                    ->extraAttributes(['data-onepage-section' => 'step_system_application']),
                Section::make(__('wpg_processing_record.step_security'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getSecurity())
                    ->extraAttributes(['data-onepage-section' => 'step_security']),
                Section::make(__('wpg_processing_record.step_geb_dpia'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getGebDpia())
                    ->extraAttributes(['data-onepage-section' => 'step_geb_dpia']),
                Section::make(__('wpg_processing_record.step_contact_person'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getContactPersons())
                    ->extraAttributes(['data-onepage-section' => 'step_contact_person']),
                Section::make(__('wpg_processing_record.step_attachments'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getAttachments())
                    ->extraAttributes(['data-onepage-section' => 'step_attachments']),
                Section::make(__('wpg_processing_record.step_remarks'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getRemarks())
                    ->extraAttributes(['data-onepage-section' => 'step_remarks']),
                Section::make(__('wpg_processing_record.step_categories_involved'))
                    ->schema(WpgProcessingRecordResourceFormSchemas::getCategoriesInvolved())
                    ->extraAttributes(['data-onepage-section' => 'step_categories_involved']),
            ]);
    }
}
