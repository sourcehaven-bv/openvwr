<?php

declare(strict_types=1);

namespace App\Filament\Resources\WpgProcessingRecordResource;

use App\Filament\Infolists\Components\DateEntry;
use App\Filament\Infolists\Components\EntityNumberEntry;
use App\Filament\Infolists\Components\ImportNumberEntry;
use App\Filament\Infolists\Components\ParentSelectEntry;
use App\Filament\Infolists\Components\RemarksEntry;
use App\Filament\Infolists\Components\Section\InformationBlockSection;
use App\Filament\Infolists\Components\SelectMultipleEntry;
use App\Filament\Infolists\Components\TextareaEntry;
use App\Filament\Infolists\Components\ToggleEntry;
use App\Filament\Infolists\Components\WpgGoalsRepeatableEntry;
use App\Filament\Infolists\Group\ProcessingRecordContactPersons;
use App\Filament\Infolists\InfolistHelper;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

use function __;

class WpgProcessingRecordResourceInfolistSchemas
{
    /**
     * @return array<Component>
     */
    public static function getProcessingName(): array
    {
        return [
            Grid::make()
                ->schema([
                    EntityNumberEntry::make(),
                    ImportNumberEntry::make(),
                ]),
            TextEntry::make('name')
                ->label(__('processing_record.name')),
            TextEntry::make('data_collection_source')
                ->label(__('general.data_collection_source')),
            TextEntry::make('wpgProcessingRecordService.name')
                ->label(__('wpg_processing_record_service.model_singular')),
            SelectMultipleEntry::make('tags.name')
                ->label(__('tag.model_plural')),
            DateEntry::make('review_at')
                ->label(__('general.review_at')),
            ParentSelectEntry::make(),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_processing_name_title'),
                __('information_blocks.wpg_processing_record.step_processing_name_info'),
                __('information_blocks.wpg_processing_record.step_processing_name_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getResponsible(): array
    {
        return [
            SelectMultipleEntry::make('responsibles.name')
                ->label(__('responsible.model_plural')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_responsible_title'),
                __('information_blocks.wpg_processing_record.step_responsible_info'),
                __('information_blocks.wpg_processing_record.step_responsible_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getProcessor(): array
    {
        return [
            ToggleEntry::make('has_processors')
                ->label(__('wpg_processing_record.has_processors')),
            SelectMultipleEntry::make('processors.name')
                ->label(__('processor.model_plural'))
                ->visible(InfolistHelper::isFieldEnabled('has_processors')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_processor_title'),
                __('information_blocks.wpg_processing_record.step_processor_info'),
                __('information_blocks.wpg_processing_record.step_processor_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getReceiver(): array
    {
        return [
            Section::make(__('wpg_processing_record.help_receiver_provisioning'))
                ->schema([
                    ToggleEntry::make('article_15')
                        ->label(__('wpg_processing_record.article_15')),
                    ToggleEntry::make('article_15_a')
                        ->label(__('wpg_processing_record.article_15_a')),
                    TextareaEntry::make('explanation_available')
                        ->label(__('wpg_processing_record.explanation_available')),
                ]),
            Section::make(__('wpg_processing_record.help_receiver_third_party'))
                ->schema([
                    ToggleEntry::make('article_16')
                        ->label(__('wpg_processing_record.article_16')),
                    ToggleEntry::make('article_17')
                        ->label(__('wpg_processing_record.article_17')),
                    ToggleEntry::make('article_18')
                        ->label(__('wpg_processing_record.article_18')),
                    ToggleEntry::make('article_19')
                        ->label(__('wpg_processing_record.article_19')),
                    ToggleEntry::make('article_20')
                        ->label(__('wpg_processing_record.article_20')),
                    ToggleEntry::make('article_22')
                        ->label(__('wpg_processing_record.article_22')),
                    ToggleEntry::make('article_23')
                        ->label(__('wpg_processing_record.article_23')),
                    ToggleEntry::make('article_24')
                        ->label(__('wpg_processing_record.article_24')),
                    TextareaEntry::make('explanation_provisioning')
                        ->label(__('wpg_processing_record.explanation_provisioning')),
                ]),
            Section::make(__('wpg_processing_record.help_receiver_transfer'))
                ->schema([
                    ToggleEntry::make('article_17_a')
                        ->label(__('wpg_processing_record.article_17_a')),
                    TextareaEntry::make('explanation_transfer')
                        ->label(__('wpg_processing_record.explanation_transfer'))
                        ->visible(InfolistHelper::isFieldEnabled('article_17_a')),
                ]),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_receiver_title'),
                __('information_blocks.wpg_processing_record.step_receiver_info'),
                __('information_blocks.wpg_processing_record.step_receiver_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getProcessingGoal(): array
    {
        return [
            WpgGoalsRepeatableEntry::make(),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_wpg_goal_title'),
                __('information_blocks.wpg_processing_record.step_wpg_goal_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getSpecialPoliceData(): array
    {
        return [
            Section::make(__('wpg_processing_record.step_special_police_data'))
                ->schema([
                    ToggleEntry::make('police_race_or_ethnicity')
                        ->label(__('wpg_processing_record.police_race_or_ethnicity')),
                    ToggleEntry::make('police_political_attitude')
                        ->label(__('wpg_processing_record.police_political_attitude')),
                    ToggleEntry::make('police_faith_or_belief')
                        ->label(__('wpg_processing_record.police_faith_or_belief')),
                    ToggleEntry::make('police_association_membership')
                        ->label(__('wpg_processing_record.police_association_membership')),
                    ToggleEntry::make('police_genetic')
                        ->label(__('wpg_processing_record.police_genetic')),
                    ToggleEntry::make('police_identification')
                        ->label(__('wpg_processing_record.police_identification')),
                    ToggleEntry::make('police_health')
                        ->label(__('wpg_processing_record.police_health')),
                    ToggleEntry::make('police_sexual_life')
                        ->label(__('wpg_processing_record.police_sexual_life')),
                ]),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_special_police_data_title'),
                __('information_blocks.wpg_processing_record.step_special_police_data_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getDecisionMaking(): array
    {
        return [
            Section::make(__('wpg_processing_record.step_decision_making'))
                ->schema([
                    ToggleEntry::make('decision_making')
                        ->label(__('wpg_processing_record.decision_making')),
                    Group::make()
                        ->visible(InfolistHelper::isFieldEnabled('decision_making'))
                        ->schema([
                            TextareaEntry::make('logic')
                                ->label(__('wpg_processing_record.logic')),
                            TextareaEntry::make('consequences')
                                ->label(__('wpg_processing_record.consequences')),
                        ]),
                ]),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_decision_making_title'),
                __('information_blocks.wpg_processing_record.step_decision_making_info'),
                __('information_blocks.wpg_processing_record.step_decision_making_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getSystems(): array
    {
        return [
            ToggleEntry::make('has_systems')
                ->label(__('wpg_processing_record.help_has_systems')),
            SelectMultipleEntry::make('systems.description')
                ->label(__('system.model_plural'))
                ->visible(InfolistHelper::isFieldEnabled('has_systems')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_system_application_title'),
                __('information_blocks.wpg_processing_record.step_system_application_info'),
                __('information_blocks.wpg_processing_record.step_system_application_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getSecurity(): array
    {
        return [
            ToggleEntry::make('has_security')
                ->label(__('wpg_processing_record.has_security')),
            Group::make()
                ->visible(InfolistHelper::isFieldEnabled('has_security'))
                ->schema([
                    Section::make(__('processor.measures'))
                        ->schema([
                            ToggleEntry::make('measures_implemented')
                                ->label(__('wpg_processing_record.measures_implemented')),
                            ToggleEntry::make('other_measures')
                                ->label(__('wpg_processing_record.other_measures')),
                            TextareaEntry::make('measures_description')
                                ->label(__('wpg_processing_record.measures_description')),
                        ]),
                    Section::make()
                        ->schema([
                            ToggleEntry::make('has_pseudonymization')
                                ->label(__('wpg_processing_record.has_pseudonymization')),
                            TextareaEntry::make('pseudonymization')
                                ->label(__('wpg_processing_record.pseudonymization'))
                                ->visible(InfolistHelper::isFieldEnabled('has_pseudonymization')),
                        ]),
                ]),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_security_title'),
                __('information_blocks.wpg_processing_record.step_security_info'),
                __('information_blocks.wpg_processing_record.step_security_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getGebDpia(): array
    {
        return [
            ToggleEntry::make('geb_pia')
                ->label(__('wpg_processing_record.geb_pia')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_geb_dpia_title'),
                __('information_blocks.wpg_processing_record.step_geb_dpia_info'),
                __('information_blocks.wpg_processing_record.step_geb_dpia_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getContactPersons(): array
    {
        return [
            ProcessingRecordContactPersons::makeGroup(),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_contact_person_title'),
                __('information_blocks.wpg_processing_record.step_contact_person_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getAttachments(): array
    {
        return [
            SelectMultipleEntry::make('documents.name')
                ->label(__('document.model_plural')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_attachments_title'),
                __('information_blocks.wpg_processing_record.step_attachments_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getRemarks(): array
    {
        return [
            RemarksEntry::make(),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_remarks_title'),
                __('information_blocks.wpg_processing_record.step_remarks_info'),
                __('information_blocks.wpg_processing_record.step_remarks_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getCategoriesInvolved(): array
    {
        return [
            Section::make(__('wpg_processing_record.step_categories_involved'))
                ->schema([
                    ToggleEntry::make('suspects')
                        ->label(__('wpg_processing_record.suspects')),
                    ToggleEntry::make('victims')
                        ->label(__('wpg_processing_record.victims')),
                    ToggleEntry::make('convicts')
                        ->label(__('wpg_processing_record.convicts')),
                    ToggleEntry::make('police_justice')
                        ->label(__('wpg_processing_record.police_justice')),
                    ToggleEntry::make('third_parties')
                        ->label(__('wpg_processing_record.third_parties')),
                    TextareaEntry::make('third_party_explanation')
                        ->label(__('wpg_processing_record.third_party_explanation'))
                        ->visible(InfolistHelper::isFieldEnabled('third_parties')),
                ]),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_categories_involved_title'),
                __('information_blocks.wpg_processing_record.step_categories_involved_info'),
            ),
        ];
    }
}
