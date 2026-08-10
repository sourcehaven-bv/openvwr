<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Filament\Actions\Exports\ExportColumn;
use App\Filament\Actions\Exports\SnapshotLatestCreatedAtColumn;
use App\Filament\Actions\Exports\SnapshotLatestEstablishedColumn;
use App\Filament\Actions\Exports\SnapshotLatestStatusColumn;
use App\Models\Wpg\WpgProcessingRecord;

use function __;

class WpgProcessingRecordExporter extends Exporter
{
    protected static ?string $model = WpgProcessingRecord::class;

    public static function getColumns(): array
    {
        return [
            // naam verwerking
            ExportColumn::make('organisation.name')
                ->label(__('organisation.model_singular')),
            ExportColumn::make('organisation.responsibleLegalEntity.name')
                ->label(__('responsible_legal_entity.model_singular')),
            ExportColumn::make('entityNumber.number')
                ->label(__('wpg_processing_record.number')),
            ExportColumn::make('wpgProcessingRecordService.name')
                ->label(__('wpg_processing_record_service.model_singular')),
            ExportColumn::make('name')
                ->label(__('processing_record.name')),
            ExportColumn::make('data_collection_source')
                ->label(__('general.data_collection_source')),
            ExportColumn::make('review_at')
                ->label(__('general.review_at')),
            ExportColumn::make('parent.entityNumber.number')
                ->label(__('general.parent')),

            // verantwoordelijke
            ExportColumn::make('responsibles.name')
                ->label(__('responsible.model_plural')),

            // verwerker
            ExportColumn::make('has_processors')
                ->label(__('wpg_processing_record.has_processors')),
            ExportColumn::make('processors.name')
                ->label(__('processor.model_plural')),

            // ontvanger
            ExportColumn::make('article_15')
                ->label(__('wpg_processing_record.article_15')),
            ExportColumn::make('article_15_a')
                ->label(__('wpg_processing_record.article_15_a')),
            ExportColumn::make('explanation_available')
                ->label(__('wpg_processing_record.explanation_available')),
            ExportColumn::make('article_16')
                ->label(__('wpg_processing_record.article_16')),
            ExportColumn::make('article_17')
                ->label(__('wpg_processing_record.article_17')),
            ExportColumn::make('article_19')
                ->label(__('wpg_processing_record.article_19')),
            ExportColumn::make('article_19')
                ->label(__('wpg_processing_record.article_19')),
            ExportColumn::make('article_20')
                ->label(__('wpg_processing_record.article_20')),
            ExportColumn::make('article_22')
                ->label(__('wpg_processing_record.article_22')),
            ExportColumn::make('article_23')
                ->label(__('wpg_processing_record.article_23')),
            ExportColumn::make('article_23')
                ->label(__('wpg_processing_record.article_24')),
            ExportColumn::make('explanation_provisioning')
                ->label(__('wpg_processing_record.explanation_provisioning')),
            ExportColumn::make('article_17_a')
                ->label(__('wpg_processing_record.article_17_a')),
            ExportColumn::make('explanation_transfer')
                ->label(__('wpg_processing_record.explanation_transfer')),

            // doel & grondslag
            ExportColumn::make('wpgGoals.description')
                ->label(__('wpg_goal.model_plural')),

            // bijzondere politiegegevens
            ExportColumn::make('police_race_or_ethnicity')
                ->label(__('wpg_processing_record.police_race_or_ethnicity')),
            ExportColumn::make('police_political_attitude')
                ->label(__('wpg_processing_record.police_political_attitude')),
            ExportColumn::make('police_faith_or_belief')
                ->label(__('wpg_processing_record.police_faith_or_belief')),
            ExportColumn::make('police_association_membership')
                ->label(__('wpg_processing_record.police_association_membership')),
            ExportColumn::make('police_genetic')
                ->label(__('wpg_processing_record.police_genetic')),
            ExportColumn::make('police_identification')
                ->label(__('wpg_processing_record.police_identification')),
            ExportColumn::make('police_health')
                ->label(__('wpg_processing_record.police_health')),
            ExportColumn::make('police_sexual_life')
                ->label(__('wpg_processing_record.police_sexual_life')),

            // besluitvorming
            ExportColumn::make('decision_making')
                ->label(__('wpg_processing_record.decision_making')),
            ExportColumn::make('logic')
                ->label(__('wpg_processing_record.logic')),
            ExportColumn::make('consequences')
                ->label(__('wpg_processing_record.consequences')),

            // systemen / applicaties
            ExportColumn::make('has_systems')
                ->label(__('wpg_processing_record.has_systems')),
            ExportColumn::make('systems.name')
                ->label(__('system.model_plural')),

            ExportColumn::make('has_algorithms')
                ->label(__('wpg_processing_record.has_algorithms')),
            ExportColumn::make('algorithmRecords.name')
                ->label(__('algorithm_record.model_plural')),

            // beveiliging
            ExportColumn::make('has_security')
                ->label(__('wpg_processing_record.has_security')),
            ExportColumn::make('security')
                ->label(__('wpg_processing_record.security')),
            ExportColumn::make('measures_implemented')
                ->label(__('processor.measures_implemented')),
            ExportColumn::make('other_measures')
                ->label(__('processor.other_measures')),
            ExportColumn::make('measures_description')
                ->label(__('processor.measures_description')),
            ExportColumn::make('has_pseudonymization')
                ->label(__('wpg_processing_record.has_pseudonymization')),
            ExportColumn::make('pseudonymization')
                ->label(__('wpg_processing_record.pseudonymization')),

            // geb (dpia)
            ExportColumn::make('geb_pia')
                ->label(__('wpg_processing_record.geb_pia')),

            // contactpersoon
            ExportColumn::make('users.name')
                ->label(__('contact_person.form_title_users')),
            ExportColumn::make('contactPersons.name')
                ->label(__('contact_person.form_title_contact_persons')),

            // opmerkingen
            ExportColumn::make('remarks')
                ->label(__('remark.model_plural')),

            // categorieën betrokkenen
            ExportColumn::make('suspects')
                ->label(__('wpg_processing_record.suspects')),
            ExportColumn::make('victims')
                ->label(__('wpg_processing_record.victims')),
            ExportColumn::make('convicts')
                ->label(__('wpg_processing_record.convicts')),
            ExportColumn::make('police_sexual_life')
                ->label(__('wpg_processing_record.police_justice')),
            ExportColumn::make('third_parties')
                ->label(__('wpg_processing_record.third_parties')),
            ExportColumn::make('third_party_explanation')
                ->label(__('wpg_processing_record.third_party_explanation')),

            // documenten
            ExportColumn::make('document_count')
                ->label(__('document.model_plural'))
                ->counts('documents'),

            // overig
            ExportColumn::make('created_at')
                ->label(__('general.created_at')),
            ExportColumn::make('updated_at')
                ->label(__('general.updated_at')),
            SnapshotLatestEstablishedColumn::make(),
            SnapshotLatestStatusColumn::make(),
            SnapshotLatestCreatedAtColumn::make(),
        ];
    }
}
