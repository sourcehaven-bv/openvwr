<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgResponsibleProcessingRecordResource;

use App\Filament\Forms\Components\Group\ProcessingRecordContactPersons;
use App\Filament\Forms\Components\OutsideEuCountryInputGroup;
use App\Filament\Forms\Components\PeriodicReviewField;
use App\Filament\Forms\Components\PublicFromField;
use App\Filament\Forms\Components\Radio\CoreEntityDataCollectionSource;
use App\Filament\Forms\Components\RemarksField;
use App\Filament\Forms\Components\Repeater\AvgGoalsRepeater;
use App\Filament\Forms\Components\Section\InformationBlockSection;
use App\Filament\Forms\Components\Section\StaticWebsiteCheckSection;
use App\Filament\Forms\Components\Select\ParentSelect;
use App\Filament\Forms\Components\Select\SelectSingleWithLookup;
use App\Filament\Forms\Components\SelectMultipleWithLookup;
use App\Filament\Forms\Components\StakeholdersRepeater;
use App\Filament\Forms\Components\TagsInput;
use App\Filament\Forms\Components\TextInput\EntityNumber;
use App\Filament\Forms\Components\TextInput\ImportNumber;
use App\Filament\Forms\FormHelper;
use App\Filament\Resources\DocumentResource\DocumentResourceForm;
use App\Filament\Resources\ProcessorResource\ProcessorResourceForm;
use App\Filament\Resources\ReceiverResource\ReceiverResourceForm;
use App\Filament\Resources\ResponsibleResource\ResponsibleResourceForm;
use App\Filament\Resources\SystemResource\SystemResourceForm;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\Document;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\System;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use function __;

class AvgResponsibleProcessingRecordResourceFormSchemas
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
                ->label(__('processing_record.name'))
                ->required()
                ->maxLength(255),
            CoreEntityDataCollectionSource::make(),
            SelectSingleWithLookup::makeWithDisabledOptions(
                'avg_responsible_processing_record_service_id',
                'avgResponsibleProcessingRecordService',
                AvgResponsibleProcessingRecordService::class,
                'name',
            )
                ->label(__('avg_responsible_processing_record_service.model_singular')),
            TagsInput::make(),
            PeriodicReviewField::make(),
            ParentSelect::make()
                ->hintIcon('heroicon-o-information-circle', __('general.parent_hint_icon_text')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_processing_name_title'),
                __('information_blocks.avg_responsible_processing_record.step_processing_name_info'),
                __('information_blocks.avg_responsible_processing_record.step_processing_name_extra_info'),
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
                ->label(__('responsible.model_plural'))
                ->required(),
            Textarea::make('responsibility_distribution')
                ->label(__('avg_responsible_processing_record.responsibility_distribution')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_responsible_title'),
                __('information_blocks.avg_responsible_processing_record.step_responsible_info'),
                __('information_blocks.avg_responsible_processing_record.step_responsible_extra_info'),
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
                ->helperText(__('avg_responsible_processing_record.help_has_processors'))
                ->label(__('avg_responsible_processing_record.has_processors'))
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
                __('information_blocks.avg_responsible_processing_record.step_processor_title'),
                __('information_blocks.avg_responsible_processing_record.step_processor_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getReceiver(): array
    {
        return [
            SelectMultipleWithLookup::makeForRelationshipWithCreate(
                'receivers',
                'receivers',
                Receiver::class,
                ReceiverResourceForm::getSchema(),
                'description',
            )
                ->label(__('receiver.model_plural')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_receiver_title'),
                __('information_blocks.avg_responsible_processing_record.step_receiver_info'),
                __('information_blocks.avg_responsible_processing_record.step_receiver_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getProcessingGoal(): array
    {
        return [
            AvgGoalsRepeater::make(),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_processing_goal_title'),
                __('information_blocks.avg_responsible_processing_record.step_processing_goal_info'),
                __('information_blocks.avg_responsible_processing_record.step_processing_goal_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getStakeholder(): array
    {
        return [
            StakeholdersRepeater::make(),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_stakeholder_data_title'),
                __('information_blocks.avg_responsible_processing_record.step_stakeholder_data_info'),
                __('information_blocks.avg_responsible_processing_record.step_stakeholder_data_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getDecisionMaking(): array
    {
        return [
            Toggle::make('decision_making')
                ->label(__('avg_responsible_processing_record.decision_making'))
                ->live(),

            Group::make()
                ->visible(FormHelper::isFieldEnabled('decision_making'))
                ->schema([
                    Textarea::make('logic')
                        ->label(__('avg_responsible_processing_record.logic'))
                        ->required(FormHelper::isFieldEnabled('decision_making')),

                    Textarea::make('importance_consequences')
                        ->label(__('avg_responsible_processing_record.importance_consequences'))
                        ->required(FormHelper::isFieldEnabled('decision_making')),
                ]),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_decision_making_title'),
                __('information_blocks.avg_responsible_processing_record.step_decision_making_info'),
                __('information_blocks.avg_responsible_processing_record.step_decision_making_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getSystem(): array
    {
        return [
            Toggle::make('has_systems')
                ->helperText(__('avg_responsible_processing_record.help_has_systems'))
                ->label(__('avg_responsible_processing_record.has_systems'))
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
                __('information_blocks.avg_responsible_processing_record.step_system_title'),
                __('information_blocks.avg_responsible_processing_record.step_system_info'),
                __('information_blocks.avg_responsible_processing_record.step_system_extra_info'),
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
                ->helperText(__('avg_responsible_processing_record.help_has_security'))
                ->label(__('avg_responsible_processing_record.has_security'))
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
                                ->label(__('avg_responsible_processing_record.has_pseudonymization'))
                                ->default(false)
                                ->live(),
                            Textarea::make('pseudonymization')
                                ->label(__('avg_responsible_processing_record.pseudonymization'))
                                ->visible(FormHelper::isFieldEnabled('has_pseudonymization')),
                        ]),
                ]),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_security_title'),
                __('information_blocks.avg_responsible_processing_record.step_security_info'),
                __('information_blocks.avg_responsible_processing_record.step_security_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getPassthrough(): array
    {
        return [
            Toggle::make('outside_eu')
                ->label(__('avg_responsible_processing_record.outside_eu'))
                ->helperText(__('avg_responsible_processing_record.help_outside_eu'))
                ->live(),

            Group::make()
                ->visible(FormHelper::isFieldEnabled('outside_eu'))
                ->schema([
                    OutsideEuCountryInputGroup::make(),

                    Group::make()
                        ->schema([
                            Toggle::make('outside_eu_protection_level')
                                ->label(__('avg_responsible_processing_record.outside_eu_protection_level'))
                                ->default(true)
                                ->live(),

                            Textarea::make('outside_eu_protection_level_description')
                                ->label(__('avg_responsible_processing_record.outside_eu_protection_level_description'))
                                ->required(FormHelper::isFieldDisabled('outside_eu_protection_level'))
                                ->visible(FormHelper::isFieldDisabled('outside_eu_protection_level')),

                            Textarea::make('outside_eu_description')
                                ->label(__('avg_responsible_processing_record.outside_eu_description')),
                        ]),
                ]),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_passthrough_title'),
                __('information_blocks.avg_responsible_processing_record.step_passthrough_info'),
                __('information_blocks.avg_responsible_processing_record.step_passthrough_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getGebDpia(): array
    {
        return [
            Toggle::make('geb_dpia_executed')
                ->label(__('avg_responsible_processing_record.geb_dpia_executed'))
                ->live(),
            Toggle::make('geb_dpia_automated')
                ->label(__('avg_responsible_processing_record.geb_dpia_automated'))
                ->live()
                ->visible(FormHelper::isFieldDisabled('geb_dpia_executed')),
            Toggle::make('geb_dpia_large_scale_processing')
                ->label(__('avg_responsible_processing_record.geb_dpia_large_scale_processing'))
                ->live()
                ->visible(FormHelper::fieldValueEquals([
                    'geb_dpia_executed' => false,
                    'geb_dpia_automated' => false,
                ])),
            Toggle::make('geb_dpia_large_scale_monitoring')
                ->label(__('avg_responsible_processing_record.geb_dpia_large_scale_monitoring'))
                ->live()
                ->visible(FormHelper::fieldValueEquals([
                    'geb_dpia_executed' => false,
                    'geb_dpia_automated' => false,
                    'geb_dpia_large_scale_processing' => false,
                ])),
            Toggle::make('geb_dpia_list_required')
                ->label(__('avg_responsible_processing_record.geb_dpia_list_required'))
                ->live()
                ->visible(FormHelper::fieldValueEquals([
                    'geb_dpia_executed' => false,
                    'geb_dpia_automated' => false,
                    'geb_dpia_large_scale_processing' => false,
                    'geb_dpia_large_scale_monitoring' => false,
                ])),
            Toggle::make('geb_dpia_criteria_wp248')
                ->label(__('avg_responsible_processing_record.geb_dpia_criteria_wp248'))
                ->live()
                ->visible(FormHelper::fieldValueEquals([
                    'geb_dpia_executed' => false,
                    'geb_dpia_automated' => false,
                    'geb_dpia_large_scale_processing' => false,
                    'geb_dpia_large_scale_monitoring' => false,
                    'geb_dpia_list_required' => false,
                ])),
            Toggle::make('geb_dpia_high_risk_freedoms')
                ->label(__('avg_responsible_processing_record.geb_dpia_high_risk_freedoms'))
                ->live()
                ->visible(FormHelper::fieldValueEquals([
                    'geb_dpia_executed' => false,
                    'geb_dpia_automated' => false,
                    'geb_dpia_large_scale_processing' => false,
                    'geb_dpia_large_scale_monitoring' => false,
                    'geb_dpia_list_required' => false,
                    'geb_dpia_criteria_wp248' => false,
                ])),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_geb_dpia_title'),
                __('information_blocks.avg_responsible_processing_record.step_geb_dpia_info'),
                __('information_blocks.avg_responsible_processing_record.step_geb_dpia_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getContactPerson(): array
    {
        return [
            ProcessingRecordContactPersons::makeGroup(),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_contact_person_title'),
                __('information_blocks.avg_responsible_processing_record.step_contact_person_info'),
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
                __('information_blocks.avg_responsible_processing_record.step_attachments_title'),
                __('information_blocks.avg_responsible_processing_record.step_attachments_info'),
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
                __('information_blocks.avg_responsible_processing_record.step_remarks_title'),
                __('information_blocks.avg_responsible_processing_record.step_remarks_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getPublish(): array
    {
        return [
            PublicFromField::makeForModel(AvgResponsibleProcessingRecord::class),
            StaticWebsiteCheckSection::makeTable(),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_publish_title'),
                __('information_blocks.avg_responsible_processing_record.step_publish_info'),
            ),
        ];
    }
}
