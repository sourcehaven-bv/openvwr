<?php

declare(strict_types=1);

namespace App\Filament\Resources\WpgProcessingRecordResource;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use App\Filament\Infolists\Components\ProcessingRecordTabs;

use function __;

class WpgProcessingRecordResourceInfolist
{
    public static function stepsInfolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->extraAttributes(['class' => 'vertical'])
            ->components([
                ProcessingRecordTabs::make()
                    ->tabs([
                        Tab::make(__('wpg_processing_record.step_processing_name'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getProcessingName()),
                        Tab::make(__('wpg_processing_record.step_responsible'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getResponsible()),
                        Tab::make(__('wpg_processing_record.step_processor'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getProcessor()),
                        Tab::make(__('wpg_processing_record.step_receiver'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getReceiver()),
                        Tab::make(__('wpg_processing_record.step_wpg_goal'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getProcessingGoal()),
                        Tab::make(__('wpg_processing_record.step_special_police_data'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getSpecialPoliceData()),
                        Tab::make(__('wpg_processing_record.step_decision_making'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getDecisionMaking()),
                        Tab::make(__('wpg_processing_record.step_system_application'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getSystems()),
                        Tab::make(__('wpg_processing_record.step_security'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getSecurity()),
                        Tab::make(__('wpg_processing_record.step_geb_dpia'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getGebDpia()),
                        Tab::make(__('wpg_processing_record.step_contact_person'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getContactPersons()),
                        Tab::make(__('wpg_processing_record.step_attachments'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getAttachments()),
                        Tab::make(__('wpg_processing_record.step_remarks'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getRemarks()),
                        Tab::make(__('wpg_processing_record.step_categories_involved'))
                            ->schema(WpgProcessingRecordResourceInfolistSchemas::getCategoriesInvolved()),
                    ]),
            ]);
    }

    public static function onePageInfolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('wpg_processing_record.step_processing_name'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getProcessingName())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_name']),
                Section::make(__('wpg_processing_record.step_responsible'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getResponsible())
                    ->extraAttributes(['data-onepage-section' => 'step_responsible']),
                Section::make(__('wpg_processing_record.step_processor'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getProcessor())
                    ->extraAttributes(['data-onepage-section' => 'step_processor']),
                Section::make(__('wpg_processing_record.step_receiver'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getReceiver())
                    ->extraAttributes(['data-onepage-section' => 'step_receiver']),
                Section::make(__('wpg_processing_record.step_wpg_goal'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getProcessingGoal())
                    ->extraAttributes(['data-onepage-section' => 'step_wpg_goal']),
                Section::make(__('wpg_processing_record.step_special_police_data'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getSpecialPoliceData())
                    ->extraAttributes(['data-onepage-section' => 'step_special_police_data']),
                Section::make(__('wpg_processing_record.step_decision_making'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getDecisionMaking())
                    ->extraAttributes(['data-onepage-section' => 'step_decision_making']),
                Section::make(__('wpg_processing_record.step_system_application'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getSystems())
                    ->extraAttributes(['data-onepage-section' => 'step_system_application']),
                Section::make(__('wpg_processing_record.step_security'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getSecurity())
                    ->extraAttributes(['data-onepage-section' => 'step_security']),
                Section::make(__('wpg_processing_record.step_geb_dpia'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getGebDpia())
                    ->extraAttributes(['data-onepage-section' => 'step_geb_dpia']),
                Section::make(__('wpg_processing_record.step_contact_person'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getContactPersons())
                    ->extraAttributes(['data-onepage-section' => 'step_contact_person']),
                Section::make(__('wpg_processing_record.step_attachments'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getAttachments())
                    ->extraAttributes(['data-onepage-section' => 'step_attachments']),
                Section::make(__('wpg_processing_record.step_remarks'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getRemarks())
                    ->extraAttributes(['data-onepage-section' => 'step_remarks']),
                Section::make(__('wpg_processing_record.step_categories_involved'))
                    ->schema(WpgProcessingRecordResourceInfolistSchemas::getCategoriesInvolved())
                    ->extraAttributes(['data-onepage-section' => 'step_categories_involved']),
            ]);
    }
}
