<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgProcessorProcessingRecordResource;

use App\Filament\Infolists\Components\ProcessingRecordTabs;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

use function __;

class AvgProcessorProcessingRecordResourceInfolist
{
    public static function stepsInfolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->extraAttributes(['class' => 'vertical'])
            ->components([
                ProcessingRecordTabs::make()
                    ->tabs([
                        Tab::make(__('avg_processor_processing_record.step_processing_name'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getProcessingName()),
                        Tab::make(__('avg_processor_processing_record.step_responsible'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getResponsible()),
                        Tab::make(__('avg_processor_processing_record.step_processor'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getProcessors()),
                        Tab::make(__('avg_processor_processing_record.step_receiver'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getReceiver()),
                        Tab::make(__('avg_processor_processing_record.step_processing_goal'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getProcessingGoal()),
                        Tab::make(__('avg_processor_processing_record.step_involved_data'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getInvolvedData()),
                        Tab::make(__('avg_processor_processing_record.step_decision_making'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getDecisionMaking()),
                        Tab::make(__('avg_processor_processing_record.step_system'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getSystem()),
                        Tab::make(__('avg_processor_processing_record.step_security'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getSecurity()),
                        Tab::make(__('avg_processor_processing_record.step_passthrough'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getPassthrough()),
                        Tab::make(__('avg_processor_processing_record.step_geb_pia'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getGebPia()),
                        Tab::make(__('avg_processor_processing_record.step_contact_person'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getContactPerson()),
                        Tab::make(__('avg_processor_processing_record.step_attachments'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getAttachments()),
                        Tab::make(__('avg_processor_processing_record.step_remarks'))
                            ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getRemarks()),
                    ]),
            ]);
    }

    public static function onePageInfolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('avg_processor_processing_record.step_processing_name'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getProcessingName())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_name']),
                Section::make(__('avg_processor_processing_record.step_responsible'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getResponsible())
                    ->extraAttributes(['data-onepage-section' => 'step_responsible']),
                Section::make(__('avg_processor_processing_record.step_processor'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getProcessors())
                    ->extraAttributes(['data-onepage-section' => 'step_processor']),
                Section::make(__('avg_processor_processing_record.step_receiver'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getReceiver())
                    ->extraAttributes(['data-onepage-section' => 'step_receiver']),
                Section::make(__('avg_processor_processing_record.step_processing_goal'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getProcessingGoal())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_goal']),
                Section::make(__('avg_processor_processing_record.step_involved_data'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getInvolvedData())
                    ->extraAttributes(['data-onepage-section' => 'step_involved_data']),
                Section::make(__('avg_processor_processing_record.step_decision_making'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getDecisionMaking())
                    ->extraAttributes(['data-onepage-section' => 'step_decision_making']),
                Section::make(__('avg_processor_processing_record.step_system'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getSystem())
                    ->extraAttributes(['data-onepage-section' => 'step_system']),
                Section::make(__('avg_processor_processing_record.step_security'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getSecurity())
                    ->extraAttributes(['data-onepage-section' => 'step_security']),
                Section::make(__('avg_processor_processing_record.step_passthrough'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getPassthrough())
                    ->extraAttributes(['data-onepage-section' => 'step_passthrough']),
                Section::make(__('avg_processor_processing_record.step_geb_pia'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getGebPia())
                    ->extraAttributes(['data-onepage-section' => 'step_geb_pia']),
                Section::make(__('avg_processor_processing_record.step_contact_person'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getContactPerson())
                    ->extraAttributes(['data-onepage-section' => 'step_contact_person']),
                Section::make(__('avg_processor_processing_record.step_attachments'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getAttachments())
                    ->extraAttributes(['data-onepage-section' => 'step_attachments']),
                Section::make(__('avg_processor_processing_record.step_remarks'))
                    ->schema(AvgProcessorProcessingRecordResourceInfolistSchemas::getRemarks())
                    ->extraAttributes(['data-onepage-section' => 'step_remarks']),
            ]);
    }
}
