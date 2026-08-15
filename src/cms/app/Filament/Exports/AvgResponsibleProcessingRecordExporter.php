<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Filament\Actions\Exports\ExportColumn;
use App\Filament\Actions\Exports\SnapshotLatestCreatedAtColumn;
use App\Filament\Actions\Exports\SnapshotLatestEstablishedColumn;
use App\Filament\Actions\Exports\SnapshotLatestStatusColumn;
use App\Models\Avg\AvgResponsibleProcessingRecord;

use function __;

class AvgResponsibleProcessingRecordExporter extends Exporter
{
    protected static ?string $model = AvgResponsibleProcessingRecord::class;

    public static function getColumns(): array
    {
        return [
            // naam verwerking
            ExportColumn::make('organisation.name')
                ->label(__('organisation.model_singular')),
            ExportColumn::make('organisation.responsibleLegalEntity.name')
                ->label(__('responsible_legal_entity.model_singular')),
            ExportColumn::make('name')
                ->label(__('processing_record.name')),
            ExportColumn::make('data_collection_source')
                ->label(__('general.data_collection_source')),
            ExportColumn::make('entityNumber.number')
                ->label(__('avg_responsible_processing_record.number')),
            ExportColumn::make('avgResponsibleProcessingRecordService.name')
                ->label(__('avg_responsible_processing_record_service.model_singular')),
            ExportColumn::make('tags.name')
                ->label(__('tag.model_plural')),
            ExportColumn::make('review_at')
                ->label(__('general.review_at')),
            ExportColumn::make('parent.entityNumber.number')
                ->label(__('general.parent')),

            // verantwoordelijke
            ExportColumn::make('responsibles.name')
                ->label(__('responsible.model_plural')),
            ExportColumn::make('responsibility_distribution')
                ->label(__('avg_responsible_processing_record.responsibility_distribution')),

            // verwerker
            ExportColumn::make('has_processors')
                ->label(__('avg_responsible_processing_record.has_processors')),
            ExportColumn::make('processors.name')
                ->label(__('processor.model_plural')),

            // ontvanger
            ExportColumn::make('receivers.description')
                ->label(__('receiver.model_plural')),

            // doel & grondslag
            ExportColumn::make('avgGoals.goal')
                ->label(__('avg_goal.model_plural')),

            // betrokkenen en gegevens
            ExportColumn::make('stakeholders.description')
                ->label(__('stakeholder.model_plural')),

            // besluitvorming
            ExportColumn::make('decision_making')
                ->label(__('avg_responsible_processing_record.decision_making')),
            ExportColumn::make('logic')
                ->label(__('avg_responsible_processing_record.logic')),
            ExportColumn::make('importance_consequences')
                ->label(__('avg_responsible_processing_record.importance_consequences')),

            // systemen / applicaties
            ExportColumn::make('has_systems')
                ->label(__('avg_responsible_processing_record.has_systems')),
            ExportColumn::make('systems.description')
                ->label(__('system.model_plural')),

            ExportColumn::make('has_algorithms')
                ->label(__('avg_responsible_processing_record.has_algorithms')),
            ExportColumn::make('algorithmRecords.name')
                ->label(__('algorithm_record.model_plural')),

            // beveiliging
            ExportColumn::make('has_security')
                ->label(__('avg_responsible_processing_record.has_security')),
            ExportColumn::make('measures_implemented')
                ->label(__('processor.measures_implemented')),
            ExportColumn::make('other_measures')
                ->label(__('processor.other_measures')),
            ExportColumn::make('measures_description')
                ->label(__('processor.measures_description')),
            ExportColumn::make('has_pseudonymization')
                ->label(__('avg_responsible_processing_record.has_pseudonymization')),
            ExportColumn::make('pseudonymization')
                ->label(__('avg_responsible_processing_record.pseudonymization')),

            // doorgifte
            ExportColumn::make('outside_eu')
                ->label(__('avg_responsible_processing_record.outside_eu')),
            ExportColumn::make('country')
                ->label(__('general.country')),
            ExportColumn::make('country_other')
                ->label(__('general.country_other')),
            ExportColumn::make('outside_eu_description')
                ->label(__('avg_responsible_processing_record.outside_eu_description')),
            ExportColumn::make('outside_eu_protection_level')
                ->label(__('avg_responsible_processing_record.outside_eu_protection_level')),
            ExportColumn::make('outside_eu_protection_level_description')
                ->label(__('avg_responsible_processing_record.outside_eu_protection_level_description')),

            // geb (dpia)
            ExportColumn::make('geb_dpia_executed')
                ->label(__('avg_responsible_processing_record.geb_dpia_executed')),
            ExportColumn::make('geb_dpia_automated')
                ->label(__('avg_responsible_processing_record.geb_dpia_automated')),
            ExportColumn::make('geb_dpia_large_scale_processing')
                ->label(__('avg_responsible_processing_record.geb_dpia_large_scale_processing')),
            ExportColumn::make('geb_dpia_large_scale_monitoring')
                ->label(__('avg_responsible_processing_record.geb_dpia_large_scale_monitoring')),
            ExportColumn::make('geb_dpia_list_required')
                ->label(__('avg_responsible_processing_record.geb_dpia_list_required')),
            ExportColumn::make('geb_dpia_criteria_wp248')
                ->label(__('avg_responsible_processing_record.geb_dpia_criteria_wp248')),
            ExportColumn::make('geb_dpia_high_risk_freedoms')
                ->label(__('avg_responsible_processing_record.geb_dpia_high_risk_freedoms')),

            // contactpersoon
            ExportColumn::make('users.name')
                ->label(__('contact_person.form_title_users')),
            ExportColumn::make('contactPersons.name')
                ->label(__('contact_person.form_title_contact_persons')),

            // documenten
            ...self::getDocumentColumns(),

            // opmerkingen
            ExportColumn::make('remarks')
                ->label(__('remark.model_plural')),

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
