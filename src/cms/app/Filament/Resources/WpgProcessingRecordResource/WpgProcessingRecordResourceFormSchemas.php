<?php

declare(strict_types=1);

namespace App\Filament\Resources\WpgProcessingRecordResource;

use App\Filament\Forms\Components\Group\ProcessingRecordContactPersons;
use App\Filament\Forms\Components\PeriodicReviewField;
use App\Filament\Forms\Components\Radio\CoreEntityDataCollectionSource;
use App\Filament\Forms\Components\RemarksField;
use App\Filament\Forms\Components\Section\InformationBlockSection;
use App\Filament\Forms\Components\Select\ParentSelect;
use App\Filament\Forms\Components\Select\SelectSingleWithLookup;
use App\Filament\Forms\Components\SelectMultipleWithLookup;
use App\Filament\Forms\Components\TextInput\EntityNumber;
use App\Filament\Forms\Components\TextInput\ImportNumber;
use App\Filament\Forms\Components\WpgGoalsRepeater;
use App\Filament\Forms\FormHelper;
use App\Filament\Resources\DocumentResource\DocumentResourceForm;
use App\Filament\Resources\ProcessorResource\ProcessorResourceForm;
use App\Filament\Resources\ResponsibleResource\ResponsibleResourceForm;
use App\Filament\Resources\SystemResource\SystemResourceForm;
use App\Models\Document;
use App\Models\Processor;
use App\Models\Responsible;
use App\Models\System;
use App\Models\Wpg\WpgProcessingRecordService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use function __;

class WpgProcessingRecordResourceFormSchemas
{
    /**
     * @return array<Component>
     */
    public static function getProcessingName(): array
    {
        return [
            Grid::make()
                ->schema([
                    EntityNumber::make(),
                    ImportNumber::make(),
                ]),
            TextInput::make('name')
                ->autofocus()
                ->required()
                ->maxLength(255)
                ->label(__('processing_record.name')),
            CoreEntityDataCollectionSource::make(),
            SelectSingleWithLookup::makeWithDisabledOptions(
                'wpg_processing_record_service_id',
                'wpgProcessingRecordService',
                WpgProcessingRecordService::class,
                'name',
            )
                ->label(__('wpg_processing_record_service.model_singular')),
            PeriodicReviewField::make(),
            ParentSelect::make()
                ->hintIcon('heroicon-o-information-circle', __('general.parent_hint_icon_text')),
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
            SelectMultipleWithLookup::makeForRelationshipWithCreate(
                'responsible_id',
                'responsibles',
                Responsible::class,
                ResponsibleResourceForm::getSchema(),
                'name',
            )
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
            Toggle::make('has_processors')
                ->helperText(__('wpg_processing_record.help_has_processors'))
                ->label(__('wpg_processing_record.has_processors'))
                ->default(false)
                ->live(),

            SelectMultipleWithLookup::makeForRelationshipWithCreate(
                'processors',
                'processors',
                Processor::class,
                ProcessorResourceForm::getSchema(),
                'name',
            )
                ->label(__('processor.model_plural'))
                ->required()
                ->visible(FormHelper::isFieldEnabled('has_processors')),
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
                    Toggle::make('article_15')
                        ->label(__('wpg_processing_record.article_15')),
                    Toggle::make('article_15_a')
                        ->label(__('wpg_processing_record.article_15_a')),
                    Textarea::make('explanation_available')
                        ->label(__('wpg_processing_record.explanation_available')),
                ]),
            Section::make(__('wpg_processing_record.help_receiver_third_party'))
                ->schema([
                    Toggle::make('article_16')
                        ->label(__('wpg_processing_record.article_16')),
                    Toggle::make('article_17')
                        ->label(__('wpg_processing_record.article_17')),
                    Toggle::make('article_18')
                        ->label(__('wpg_processing_record.article_18')),
                    Toggle::make('article_19')
                        ->label(__('wpg_processing_record.article_19')),
                    Toggle::make('article_20')
                        ->label(__('wpg_processing_record.article_20')),
                    Toggle::make('article_22')
                        ->label(__('wpg_processing_record.article_22')),
                    Toggle::make('article_23')
                        ->label(__('wpg_processing_record.article_23')),
                    Toggle::make('article_24')
                        ->label(__('wpg_processing_record.article_24')),
                    Textarea::make('explanation_provisioning')
                        ->label(__('wpg_processing_record.explanation_provisioning')),
                ]),
            Section::make(__('wpg_processing_record.help_receiver_transfer'))
                ->schema([
                    Toggle::make('article_17_a')
                        ->label(__('wpg_processing_record.article_17_a'))
                        ->live(),
                    Textarea::make('explanation_transfer')
                        ->label(__('wpg_processing_record.explanation_transfer'))
                        ->required()
                        ->visible(FormHelper::isFieldEnabled('article_17_a')),
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
            WpgGoalsRepeater::make(),
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
                    Toggle::make('police_race_or_ethnicity')
                        ->label(__('wpg_processing_record.police_race_or_ethnicity')),
                    Toggle::make('police_political_attitude')
                        ->label(__('wpg_processing_record.police_political_attitude')),
                    Toggle::make('police_faith_or_belief')
                        ->label(__('wpg_processing_record.police_faith_or_belief')),
                    Toggle::make('police_association_membership')
                        ->label(__('wpg_processing_record.police_association_membership')),
                    Toggle::make('police_genetic')
                        ->label(__('wpg_processing_record.police_genetic')),
                    Toggle::make('police_identification')
                        ->label(__('wpg_processing_record.police_identification')),
                    Toggle::make('police_health')
                        ->label(__('wpg_processing_record.police_health')),
                    Toggle::make('police_sexual_life')
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
                    Toggle::make('decision_making')
                        ->label(__('wpg_processing_record.decision_making'))
                        ->live(),

                    Group::make()
                        ->visible(FormHelper::isFieldEnabled('decision_making'))
                        ->schema([
                            Textarea::make('logic')
                                ->maxLength(255)
                                ->placeholder(__('wpg_processing_record.logic'))
                                ->label(__('wpg_processing_record.logic'))
                                ->required(),
                            Textarea::make('consequences')
                                ->maxLength(255)
                                ->label(__('wpg_processing_record.consequences'))
                                ->required(),
                        ]),
                    InformationBlockSection::makeCollapsible(
                        __('information_blocks.wpg_processing_record.step_decision_making_title'),
                        __('information_blocks.wpg_processing_record.step_decision_making_info'),
                        __('information_blocks.wpg_processing_record.step_decision_making_extra_info'),
                    ),
                ]),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getSystems(): array
    {
        return [
            Toggle::make('has_systems')
                ->helperText(__('wpg_processing_record.help_has_systems'))
                ->label(__('wpg_processing_record.has_systems'))
                ->default(false)
                ->live(),

            SelectMultipleWithLookup::makeForRelationshipWithCreate(
                'systems',
                'systems',
                System::class,
                SystemResourceForm::getSchema(),
                'description',
            )
                ->label(__('system.model_plural'))
                ->required()
                ->visible(FormHelper::isFieldEnabled('has_systems')),
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
            Toggle::make('has_security')
                ->label(__('wpg_processing_record.has_security'))
                ->helperText(__('wpg_processing_record.help_has_security'))
                ->default(false)
                ->live(),

            Group::make()
                ->visible(FormHelper::isFieldEnabled('has_security'))
                ->schema([
                    Section::make(__('processor.measures'))
                        ->schema([
                            Checkbox::make('measures_implemented')
                                ->label(__('processor.measures_implemented')),
                            Checkbox::make('other_measures')
                                ->label(__('processor.other_measures')),
                            Textarea::make('measures_description')
                                ->label(__('processor.measures_description')),
                        ]),

                    Section::make()
                        ->schema([
                            Toggle::make('has_pseudonymization')
                                ->label(__('wpg_processing_record.has_pseudonymization'))
                                ->default(false)
                                ->live(),
                            Textarea::make('pseudonymization')
                                ->label(__('wpg_processing_record.pseudonymization'))
                                ->visible(FormHelper::isFieldEnabled('has_pseudonymization')),
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
            Toggle::make('geb_pia')
                ->label(__('wpg_processing_record.geb_pia'))
                ->helperText(__('wpg_processing_record.help_geb_pia')),
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
            SelectMultipleWithLookup::makeForRelationshipWithCreate(
                'document_id',
                'documents',
                Document::class,
                DocumentResourceForm::getSchema(),
                'name',
            )
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
            RemarksField::make()
                ->mutateRelationshipDataBeforeCreateUsing(FormHelper::addAuthFields())
                ->mutateRelationshipDataBeforeSaveUsing(FormHelper::addAuthFields()),
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
                    Toggle::make('suspects')
                        ->label(__('wpg_processing_record.suspects')),
                    Toggle::make('victims')
                        ->label(__('wpg_processing_record.victims')),
                    Toggle::make('convicts')
                        ->label(__('wpg_processing_record.convicts')),
                    Toggle::make('police_justice')
                        ->label(__('wpg_processing_record.police_justice')),
                    Toggle::make('third_parties')
                        ->label(__('wpg_processing_record.third_parties'))
                        ->live(),

                    Textarea::make('third_party_explanation')
                        ->required()
                        ->placeholder(__('wpg_processing_record.third_party_explanation'))
                        ->label(__('wpg_processing_record.third_party_explanation'))
                        ->visible(FormHelper::isFieldEnabled('third_parties')),
                ]),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.wpg_processing_record.step_categories_involved_title'),
                __('information_blocks.wpg_processing_record.step_categories_involved_info'),
            ),
        ];
    }
}
