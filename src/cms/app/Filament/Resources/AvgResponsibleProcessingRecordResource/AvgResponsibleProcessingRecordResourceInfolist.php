<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgResponsibleProcessingRecordResource;

use App\Config\Feature;
use App\Filament\Infolists\Components\ProcessingRecordTabs;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Infolist;

use function __;

class AvgResponsibleProcessingRecordResourceInfolist
{
    public static function stepsInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->extraAttributes(['class' => 'vertical'])
            ->schema([
                ProcessingRecordTabs::make()
                    ->tabs([
                        Tab::make(__('avg_responsible_processing_record.step_processing_name'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getProcessingName()),
                        Tab::make(__('avg_responsible_processing_record.step_responsible'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getResponsible()),
                        Tab::make(__('avg_responsible_processing_record.step_processor'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getProcessor()),
                        Tab::make(__('avg_responsible_processing_record.step_receiver'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getReceiver()),
                        Tab::make(__('avg_responsible_processing_record.step_processing_goal'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getProcessingGoal()),
                        Tab::make(__('avg_responsible_processing_record.step_stakeholder_data'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getStakeholder()),
                        Tab::make(__('avg_responsible_processing_record.step_decision_making'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getDecisionMaking()),
                        Tab::make(__('avg_responsible_processing_record.step_system'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getSystem()),
                        Tab::make(__('avg_responsible_processing_record.step_security'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getSecurity()),
                        Tab::make(__('avg_responsible_processing_record.step_passthrough'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getPassthrough()),
                        Tab::make(__('avg_responsible_processing_record.step_geb_dpia'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getGebDpia()),
                        Tab::make(__('avg_responsible_processing_record.step_contact_person'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getContactPerson()),
                        Tab::make(__('avg_responsible_processing_record.step_attachments'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getAttachments()),
                        Tab::make(__('avg_responsible_processing_record.step_remarks'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getRemarks()),
                        Tab::make(__('avg_responsible_processing_record.step_publish'))
                            ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getPublish())
                            ->visible(Feature::publishingEnabled()),
                    ]),
            ]);
    }

    public static function onePageInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('avg_responsible_processing_record.step_processing_name'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getProcessingName())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_name']),
                Section::make(__('avg_responsible_processing_record.step_responsible'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getResponsible())
                    ->extraAttributes(['data-onepage-section' => 'step_responsible']),
                Section::make(__('avg_responsible_processing_record.step_processor'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getProcessor())
                    ->extraAttributes(['data-onepage-section' => 'step_processor']),
                Section::make(__('avg_responsible_processing_record.step_receiver'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getReceiver())
                    ->extraAttributes(['data-onepage-section' => 'step_receiver']),
                Section::make(__('avg_responsible_processing_record.step_processing_goal'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getProcessingGoal())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_goal']),
                Section::make(__('avg_responsible_processing_record.step_stakeholder_data'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getStakeholder())
                    ->extraAttributes(['data-onepage-section' => 'step_stakeholder_data']),
                Section::make(__('avg_responsible_processing_record.step_decision_making'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getDecisionMaking())
                    ->extraAttributes(['data-onepage-section' => 'step_decision_making']),
                Section::make(__('avg_responsible_processing_record.step_system'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getSystem())
                    ->extraAttributes(['data-onepage-section' => 'step_system']),
                Section::make(__('avg_responsible_processing_record.step_security'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getSecurity())
                    ->extraAttributes(['data-onepage-section' => 'step_security']),
                Section::make(__('avg_responsible_processing_record.step_passthrough'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getPassthrough())
                    ->extraAttributes(['data-onepage-section' => 'step_passthrough']),
                Section::make(__('avg_responsible_processing_record.step_geb_dpia'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getGebDpia())
                    ->extraAttributes(['data-onepage-section' => 'step_geb_dpia']),
                Section::make(__('avg_responsible_processing_record.step_contact_person'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getContactPerson())
                    ->extraAttributes(['data-onepage-section' => 'step_contact_person']),
                Section::make(__('avg_responsible_processing_record.step_attachments'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getAttachments())
                    ->extraAttributes(['data-onepage-section' => 'step_attachments']),
                Section::make(__('avg_responsible_processing_record.step_remarks'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getRemarks())
                    ->extraAttributes(['data-onepage-section' => 'step_remarks']),
                Section::make(__('avg_responsible_processing_record.step_publish'))
                    ->schema(AvgResponsibleProcessingRecordResourceInfolistSchemas::getPublish())
                    ->extraAttributes(['data-onepage-section' => 'step_publish'])
                    ->visible(Feature::publishingEnabled()),
            ]);
    }
}
