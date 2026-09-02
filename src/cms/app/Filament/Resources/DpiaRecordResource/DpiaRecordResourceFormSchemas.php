<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaRecordResource;

use App\Enums\Dpia\DpiaSubjectType;
use App\Filament\Forms\Components\RelationTable;
use App\Filament\Forms\Components\RelationTableColumns;
use App\Filament\Forms\Components\RemarksField;
use App\Filament\Forms\Components\Section\InformationBlockSection;
use App\Filament\Forms\Components\TagsInput;
use App\Filament\Forms\Components\TextInput\EntityNumber;
use App\Filament\Forms\FormHelper;
use App\Filament\Resources\DocumentResource\DocumentResourceForm;
use App\Filament\Resources\SystemResource\SystemResourceForm;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Models\System;
use App\Services\Dpia\SpecialCategoriesSummary;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

use function __;

/**
 * The form sections of the DPIA, one method per paragraph of Model DPIA
 * Rijksdienst v3.0. Both the wizard and the one-page layout consume these, so
 * a field only ever has to be defined once.
 */
class DpiaRecordResourceFormSchemas
{
    /**
     * @return array<Component>
     */
    public static function getGeneral(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.dpia_record.step_general_title'),
                __('information_blocks.dpia_record.step_general_info'),
                __('information_blocks.dpia_record.step_general_extra_info'),
            ),
            EntityNumber::make(),
            TextInput::make('name')
                ->label(__('dpia_record.name'))
                ->helperText(__('dpia_record.help_name'))
                ->required()
                ->maxLength(255),
            Radio::make('subject_type')
                ->label(__('dpia_record.subject_type'))
                ->helperText(__('dpia_record.help_subject_type'))
                ->options(DpiaSubjectType::options())
                ->default(DpiaSubjectType::PROCESSING->value)
                ->required(),
            Select::make('dpia_prescan_record_id')
                ->label(__('dpia_record.prescan'))
                ->helperText(__('dpia_record.help_prescan'))
                ->relationship('dpiaPrescanRecord', 'name')
                ->searchable()
                ->preload(),
            TagsInput::make(),
        ];
    }

    /**
     * 1. Voorstel
     *
     * @return array<Component>
     */
    public static function getProposal(): array
    {
        return [
            self::partAInformation(),
            Textarea::make('proposal_description')
                ->label(__('dpia_record.proposal_description'))
                ->helperText(__('dpia_record.help_proposal_description'))
                ->rows(5)
                ->columnSpanFull(),
            Textarea::make('proposal_motivation')
                ->label(__('dpia_record.proposal_motivation'))
                ->helperText(__('dpia_record.help_proposal_motivation'))
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    /**
     * 4. Technieken en methoden
     *
     * @return array<Component>
     */
    public static function getTechniques(): array
    {
        return [
            Textarea::make('techniques_description')
                ->label(__('dpia_record.techniques_description'))
                ->helperText(__('dpia_record.help_techniques_description'))
                ->rows(4)
                ->columnSpanFull(),
            Grid::make()
                ->schema([
                    Toggle::make('automated_decision_making')
                        ->label(__('dpia_record.automated_decision_making'))
                        ->live(),
                    Toggle::make('profiling')
                        ->label(__('dpia_record.profiling'))
                        ->live(),
                    Toggle::make('cloud_processing')
                        ->label(__('dpia_record.cloud_processing'))
                        ->live(),
                    Toggle::make('big_data_processing')
                        ->label(__('dpia_record.big_data_processing'))
                        ->live(),
                ]),
            Textarea::make('techniques_explanation')
                ->label(__('dpia_record.techniques_explanation'))
                ->helperText(__('dpia_record.help_techniques_explanation'))
                ->rows(4)
                ->columnSpanFull()
                ->visible(static function (Get $get): bool {
                    return (bool) $get('automated_decision_making')
                        || (bool) $get('profiling')
                        || (bool) $get('cloud_processing')
                        || (bool) $get('big_data_processing');
                }),
        ];
    }

    /**
     * 5. Verwerkingsdoeleinden
     *
     * @return array<Component>
     */
    public static function getPurposes(): array
    {
        return [
            Textarea::make('purpose_description')
                ->label(__('dpia_record.purpose_description'))
                ->helperText(__('dpia_record.help_purpose_description'))
                ->rows(5)
                ->columnSpanFull(),
        ];
    }

    /**
     * 6. Betrokken partijen
     *
     * @return array<Component>
     */
    public static function getParties(): array
    {
        return [
            Textarea::make('parties_description')
                ->label(__('dpia_record.parties_description'))
                ->helperText(__('dpia_record.help_parties_description'))
                ->rows(5)
                ->columnSpanFull(),
            Textarea::make('parties_access')
                ->label(__('dpia_record.parties_access'))
                ->helperText(__('dpia_record.help_parties_access'))
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    /**
     * 7. Belangen
     *
     * @return array<Component>
     */
    public static function getInterests(): array
    {
        return [
            Textarea::make('interests_description')
                ->label(__('dpia_record.interests_description'))
                ->helperText(__('dpia_record.help_interests_description'))
                ->rows(4)
                ->columnSpanFull(),
            Textarea::make('interests_data_subjects')
                ->label(__('dpia_record.interests_data_subjects'))
                ->helperText(__('dpia_record.help_interests_data_subjects'))
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    /**
     * 8. Verwerkingslocaties
     *
     * @return array<Component>
     */
    public static function getLocations(): array
    {
        return [
            Textarea::make('processing_locations')
                ->label(__('dpia_record.processing_locations'))
                ->helperText(__('dpia_record.help_processing_locations'))
                ->rows(3)
                ->columnSpanFull(),
            Toggle::make('outside_eea')
                ->label(__('dpia_record.outside_eea'))
                ->live(),
            Textarea::make('transfer_mechanism')
                ->label(__('dpia_record.transfer_mechanism'))
                ->helperText(__('dpia_record.help_transfer_mechanism'))
                ->rows(3)
                ->columnSpanFull()
                ->visible(FormHelper::isFieldEnabled('outside_eea')),
            Textarea::make('transfer_safeguards')
                ->label(__('dpia_record.transfer_safeguards'))
                ->helperText(__('dpia_record.help_transfer_safeguards'))
                ->rows(3)
                ->columnSpanFull()
                ->visible(FormHelper::isFieldEnabled('outside_eea')),
        ];
    }

    /**
     * 9. Juridisch en beleidsmatig kader
     *
     * @return array<Component>
     */
    public static function getLegalFramework(): array
    {
        return [
            Textarea::make('legal_policy_framework')
                ->label(__('dpia_record.legal_policy_framework'))
                ->helperText(__('dpia_record.help_legal_policy_framework'))
                ->rows(5)
                ->columnSpanFull(),
        ];
    }

    /**
     * 10. Bewaartermijnen
     *
     * @return array<Component>
     */
    public static function getRetention(): array
    {
        return [
            Textarea::make('retention_periods')
                ->label(__('dpia_record.retention_periods'))
                ->helperText(__('dpia_record.help_retention_periods'))
                ->rows(4)
                ->columnSpanFull(),
            Textarea::make('retention_motivation')
                ->label(__('dpia_record.retention_motivation'))
                ->helperText(__('dpia_record.help_retention_motivation'))
                ->rows(4)
                ->columnSpanFull(),
            Textarea::make('retention_responsible')
                ->label(__('dpia_record.retention_responsible'))
                ->helperText(__('dpia_record.help_retention_responsible'))
                ->rows(3)
                ->columnSpanFull(),
        ];
    }

    /**
     * 11. Rechtsgrond
     *
     * @return array<Component>
     */
    public static function getLegalBasis(): array
    {
        return [
            self::partBInformation(),
            Textarea::make('legal_basis')
                ->label(__('dpia_record.legal_basis'))
                ->helperText(__('dpia_record.help_legal_basis'))
                ->rows(4)
                ->columnSpanFull(),
            Textarea::make('legal_basis_conditions')
                ->label(__('dpia_record.legal_basis_conditions'))
                ->helperText(__('dpia_record.help_legal_basis_conditions'))
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    /**
     * 12. Bijzondere persoonsgegevens
     *
     * @return array<Component>
     */
    public static function getSpecialCategories(): array
    {
        return [
            // Paragraaf 12 is not asked again here: which gegevens need a ground
            // follows from the classification in paragraaf 2, and the ground is
            // recorded next to the gegeven it justifies. Asking a second time
            // is how a DPIA ends up contradicting itself.
            Placeholder::make('special_categories_summary')
                ->hiddenLabel()
                ->content(static fn (Get $get): HtmlString => SpecialCategoriesSummary::render($get))
                ->columnSpanFull(),
            Textarea::make('special_categories_exception')
                ->label(__('dpia_record.special_categories_additional'))
                ->helperText(__('dpia_record.help_special_categories_additional'))
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    /**
     * 13. Doelbinding
     *
     * @return array<Component>
     */
    public static function getPurposeLimitation(): array
    {
        return [
            Toggle::make('further_processing')
                ->label(__('dpia_record.further_processing'))
                ->helperText(__('dpia_record.help_further_processing'))
                ->live(),
            Textarea::make('purpose_limitation')
                ->label(__('dpia_record.purpose_limitation'))
                ->helperText(__('dpia_record.help_purpose_limitation'))
                ->rows(5)
                ->columnSpanFull()
                ->visible(FormHelper::isFieldEnabled('further_processing')),
        ];
    }

    /**
     * 14. Noodzaak en evenredigheid
     *
     * @return array<Component>
     */
    public static function getNecessity(): array
    {
        return [
            Textarea::make('necessity_proportionality')
                ->label(__('dpia_record.necessity_proportionality'))
                ->helperText(__('dpia_record.help_necessity_proportionality'))
                ->rows(5)
                ->columnSpanFull(),
            Textarea::make('necessity_subsidiarity')
                ->label(__('dpia_record.necessity_subsidiarity'))
                ->helperText(__('dpia_record.help_necessity_subsidiarity'))
                ->rows(5)
                ->columnSpanFull(),
        ];
    }

    /**
     * 15. Rechten van de betrokkenen
     *
     * @return array<Component>
     */
    public static function getRights(): array
    {
        return [
            Textarea::make('data_subject_rights_procedure')
                ->label(__('dpia_record.data_subject_rights_procedure'))
                ->helperText(__('dpia_record.help_data_subject_rights_procedure'))
                ->rows(6)
                ->columnSpanFull(),
            Toggle::make('rights_restricted')
                ->label(__('dpia_record.rights_restricted'))
                ->live(),
            Textarea::make('rights_restriction_basis')
                ->label(__('dpia_record.rights_restriction_basis'))
                ->helperText(__('dpia_record.help_rights_restriction_basis'))
                ->rows(4)
                ->columnSpanFull()
                ->visible(FormHelper::isFieldEnabled('rights_restricted')),
        ];
    }

    /**
     * Koppelingen naar verwerkingen en systemen.
     *
     * @return array<Component>
     */
    public static function getRelations(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.dpia_record.step_relations_title'),
                __('information_blocks.dpia_record.step_relations_info'),
            ),
            RelationTable::makeForRelationship(
                'avg_responsible_processing_records',
                'avgResponsibleProcessingRecords',
                AvgResponsibleProcessingRecord::class,
                'name',
                RelationTableColumns::for(AvgResponsibleProcessingRecord::class),
            )
                ->label(__('dpia_record.avg_responsible_processing_records'))
                ->helperText(__('dpia_record.help_avg_responsible_processing_records')),
            RelationTable::makeForRelationship(
                'systems',
                'systems',
                System::class,
                'description',
                RelationTableColumns::for(System::class),
                SystemResourceForm::getSchema(),
            )
                ->label(__('dpia_record.systems')),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getAttachments(): array
    {
        return [
            RelationTable::makeForRelationship(
                'document_id',
                'documents',
                Document::class,
                'name',
                RelationTableColumns::for(Document::class),
                DocumentResourceForm::getSchema(),
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
            RemarksField::make(),
        ];
    }

    private static function partAInformation(): Component
    {
        return InformationBlockSection::makeCollapsible(
            __('information_blocks.dpia_record.part_a_title'),
            __('information_blocks.dpia_record.part_a_info'),
        );
    }

    private static function partBInformation(): Component
    {
        return InformationBlockSection::makeCollapsible(
            __('information_blocks.dpia_record.part_b_title'),
            __('information_blocks.dpia_record.part_b_info'),
        );
    }
}
