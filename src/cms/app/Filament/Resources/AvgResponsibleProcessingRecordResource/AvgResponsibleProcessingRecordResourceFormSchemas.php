<?php

declare(strict_types=1);

namespace App\Filament\Resources\AvgResponsibleProcessingRecordResource;

use App\Filament\Forms\Components\DataLossToggle;
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
use App\Filament\Forms\GebDpiaQuestionnaire;
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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use function __;
use function array_map;

class AvgResponsibleProcessingRecordResourceFormSchemas
{
    /**
     * @return array<Component>
     */
    public static function getProcessingName(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_processing_name_title'),
                __('information_blocks.avg_responsible_processing_record.step_processing_name_info'),
                __('information_blocks.avg_responsible_processing_record.step_processing_name_extra_info'),
            ),
            Grid::make()
                ->schema([
                    EntityNumber::make(),
                    ImportNumber::make(),
                ]),
            TextInput::make('name')
                ->label(__('processing_record.name'))
                ->helperText(__('processing_record.name_help'))
                ->required()
                ->maxLength(255),
            CoreEntityDataCollectionSource::make(),
            SelectSingleWithLookup::makeWithDisabledOptions(
                'avg_responsible_processing_record_service_id',
                'avgResponsibleProcessingRecordService',
                AvgResponsibleProcessingRecordService::class,
                'name',
            )
                ->label(__('avg_responsible_processing_record_service.model_singular'))
                ->helperText(__('avg_responsible_processing_record.help_service')),
            TagsInput::make(),
            PeriodicReviewField::make(),
            ParentSelect::make()
                ->helperText(__('general.parent_help')),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getResponsible(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_responsible_title'),
                __('information_blocks.avg_responsible_processing_record.step_responsible_info'),
                __('information_blocks.avg_responsible_processing_record.step_responsible_extra_info'),
            ),
            SelectMultipleWithLookup::makeForRelationshipWithCreate(
                'responsible_id',
                'responsibles',
                Responsible::class,
                ResponsibleResourceForm::getSchema(),
                'name',
            )
                ->label(__('responsible.model_plural'))
                ->helperText(__('avg_responsible_processing_record.help_responsible'))
                ->required(),
            Textarea::make('responsibility_distribution')
                ->label(__('avg_responsible_processing_record.responsibility_distribution'))
                ->helperText(__('avg_responsible_processing_record.help_responsibility_distribution')),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getProcessor(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_processor_title'),
                __('information_blocks.avg_responsible_processing_record.step_processor_info'),
            ),
            DataLossToggle::makeWithConfirmation(
                'has_processors',
                ['processors'],
                __('avg_responsible_processing_record.warn_has_processors_data_loss'),
            )
                ->helperText(__('avg_responsible_processing_record.help_has_processors'))
                ->label(__('avg_responsible_processing_record.has_processors'))
                ->default(false),
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
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getReceiver(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_receiver_title'),
                __('information_blocks.avg_responsible_processing_record.step_receiver_info'),
                __('information_blocks.avg_responsible_processing_record.step_receiver_extra_info'),
            ),
            SelectMultipleWithLookup::makeForRelationshipWithCreate(
                'receivers',
                'receivers',
                Receiver::class,
                ReceiverResourceForm::getSchema(),
                'description',
            )
                ->label(__('receiver.model_plural'))
                ->helperText(__('avg_responsible_processing_record.help_receivers')),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getProcessingGoal(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_processing_goal_title'),
                __('information_blocks.avg_responsible_processing_record.step_processing_goal_info'),
                __('information_blocks.avg_responsible_processing_record.step_processing_goal_extra_info'),
            ),
            AvgGoalsRepeater::make(),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getStakeholder(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_stakeholder_data_title'),
                __('information_blocks.avg_responsible_processing_record.step_stakeholder_data_info'),
                __('information_blocks.avg_responsible_processing_record.step_stakeholder_data_extra_info'),
            ),
            StakeholdersRepeater::make(),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getDecisionMaking(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_decision_making_title'),
                __('information_blocks.avg_responsible_processing_record.step_decision_making_info'),
                __('information_blocks.avg_responsible_processing_record.step_decision_making_extra_info'),
            ),
            Toggle::make('decision_making')
                ->label(__('avg_responsible_processing_record.decision_making'))
                ->helperText(__('avg_responsible_processing_record.help_decision_making'))
                ->live(),

            Group::make()
                ->visible(FormHelper::isFieldEnabled('decision_making'))
                ->schema([
                    Textarea::make('logic')
                        ->label(__('avg_responsible_processing_record.logic'))
                        ->helperText(__('avg_responsible_processing_record.help_logic'))
                        ->required(FormHelper::isFieldEnabled('decision_making')),

                    Textarea::make('importance_consequences')
                        ->label(__('avg_responsible_processing_record.importance_consequences'))
                        ->helperText(__('avg_responsible_processing_record.help_importance_consequences'))
                        ->required(FormHelper::isFieldEnabled('decision_making')),
                ]),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getSystem(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_system_title'),
                __('information_blocks.avg_responsible_processing_record.step_system_info'),
                __('information_blocks.avg_responsible_processing_record.step_system_extra_info'),
            ),
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
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getSecurity(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_security_title'),
                __('information_blocks.avg_responsible_processing_record.step_security_info'),
                __('information_blocks.avg_responsible_processing_record.step_security_extra_info'),
            ),
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
                                ->label(__('processor.measures_implemented'))
                                ->helperText(__('avg_responsible_processing_record.help_measures_implemented')),
                            Checkbox::make('other_measures')
                                ->label(__('processor.other_measures'))
                                ->helperText(__('avg_responsible_processing_record.help_other_measures')),
                            Textarea::make('measures_description')
                                ->label(__('processor.measures_description'))
                                ->helperText(__('avg_responsible_processing_record.help_measures_description')),
                        ]),

                    Section::make()
                        ->schema([
                            Toggle::make('has_pseudonymization')
                                ->label(__('avg_responsible_processing_record.has_pseudonymization'))
                                ->helperText(__('avg_responsible_processing_record.help_has_pseudonymization'))
                                ->default(false)
                                ->live(),
                            Textarea::make('pseudonymization')
                                ->label(__('avg_responsible_processing_record.pseudonymization'))
                                ->helperText(__('avg_responsible_processing_record.help_pseudonymization'))
                                ->visible(FormHelper::isFieldEnabled('has_pseudonymization')),
                        ]),
                ]),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getPassthrough(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_passthrough_title'),
                __('information_blocks.avg_responsible_processing_record.step_passthrough_info'),
                __('information_blocks.avg_responsible_processing_record.step_passthrough_extra_info'),
            ),
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
                                ->helperText(__('avg_responsible_processing_record.help_outside_eu_protection_level'))
                                ->default(true)
                                ->live(),

                            Textarea::make('outside_eu_protection_level_description')
                                ->label(__('avg_responsible_processing_record.outside_eu_protection_level_description'))
                                ->helperText(__('avg_responsible_processing_record.help_outside_eu_protection_level_description'))
                                ->required(FormHelper::isFieldDisabled('outside_eu_protection_level'))
                                ->visible(FormHelper::isFieldDisabled('outside_eu_protection_level')),

                            Textarea::make('outside_eu_description')
                                ->label(__('avg_responsible_processing_record.outside_eu_description'))
                                ->helperText(__('avg_responsible_processing_record.help_outside_eu_description')),
                        ]),
                ]),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getGebDpia(): array
    {
        // Progressive "is a GEB (DPIA) mandatory?" questionnaire. The six
        // criteria (GDPR art. 35(3)(a)/(b)/(c), 35(4) and the WP248 criteria)
        // are OR-ed: the first "ja" concludes that a GEB is mandatory and the
        // remaining questions are skipped. If a GEB was already carried out
        // (geb_dpia_executed) the questionnaire is moot and stays hidden.
        // GebDpiaQuestionnaire keeps the visibility, outcome and reset-on-save
        // logic in one place, shared with the infolist.
        $criteria = GebDpiaQuestionnaire::CRITERIA;

        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_geb_dpia_title'),
                __('information_blocks.avg_responsible_processing_record.step_geb_dpia_info'),
                __('information_blocks.avg_responsible_processing_record.step_geb_dpia_extra_info'),
            ),
            Toggle::make('geb_dpia_executed')
                ->label(__('avg_responsible_processing_record.geb_dpia_executed'))
                ->helperText(__('avg_responsible_processing_record.help_geb_dpia_executed'))
                ->live(),
            Section::make(__('avg_responsible_processing_record.geb_dpia_criteria_heading'))
                ->description(__('avg_responsible_processing_record.geb_dpia_criteria_description'))
                ->visible(FormHelper::isFieldDisabled('geb_dpia_executed'))
                ->schema(array_map(
                    static fn (string $field): Toggle => GebDpiaQuestionnaire::criterionToggle($field),
                    $criteria,
                )),
            Placeholder::make('geb_dpia_outcome')
                ->label(__('avg_responsible_processing_record.geb_dpia_outcome_label'))
                ->content(GebDpiaQuestionnaire::outcomeContent()),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getContactPerson(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_contact_person_title'),
                __('information_blocks.avg_responsible_processing_record.step_contact_person_info'),
            ),
            ProcessingRecordContactPersons::makeGroup(),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getAttachments(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_attachments_title'),
                __('information_blocks.avg_responsible_processing_record.step_attachments_info'),
            ),
            SelectMultipleWithLookup::makeForRelationshipWithCreate(
                'document_id',
                'documents',
                Document::class,
                DocumentResourceForm::getSchema(),
                'name',
            )
                ->label(__('document.model_plural')),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getRemarks(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_remarks_title'),
                __('information_blocks.avg_responsible_processing_record.step_remarks_info'),
            ),
            RemarksField::make()
                ->mutateRelationshipDataBeforeCreateUsing(FormHelper::addAuthFields())
                ->mutateRelationshipDataBeforeSaveUsing(FormHelper::addAuthFields()),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getPublish(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.avg_responsible_processing_record.step_publish_title'),
                __('information_blocks.avg_responsible_processing_record.step_publish_info'),
            ),
            PublicFromField::makeForModel(AvgResponsibleProcessingRecord::class),
            StaticWebsiteCheckSection::makeTable(),
        ];
    }
}
