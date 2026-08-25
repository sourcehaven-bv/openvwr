<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Filament\Actions\Exports\ExportColumn;
use App\Filament\Actions\Exports\SnapshotLatestCreatedAtColumn;
use App\Filament\Actions\Exports\SnapshotLatestEstablishedColumn;
use App\Filament\Actions\Exports\SnapshotLatestStatusColumn;
use App\Models\Avg\AvgProcessorProcessingRecord;

use function __;

class AvgProcessorProcessingRecordExporter extends Exporter
{
    protected static ?string $model = AvgProcessorProcessingRecord::class;

    public static function getColumns(): array
    {
        return [
            // naam verwerking
            ExportColumn::make('organisation.name')
                ->label(__('organisation.model_singular')),
            ExportColumn::make('organisation.responsibleLegalEntity.name')
                ->label(__('responsible_legal_entity.model_singular')),
            ExportColumn::make('entityNumber.number')
                ->label(__('avg_processor_processing_record.number')),
            ExportColumn::make('avgProcessorProcessingRecordService.name')
                ->label(__('avg_processor_processing_record_service.model_singular')),
            ExportColumn::make('name')
                ->label(__('processing_record.name')),
            ExportColumn::make('tags.name')
                ->label(__('tag.model_plural')),
            ExportColumn::make('data_collection_source')
                ->label(__('general.data_collection_source')),
            ExportColumn::make('review_at')
                ->label(__('general.review_at')),
            ExportColumn::make('parent.entityNumber.number')
                ->label(__('general.parent')),

            // verwerkingsverwantwoordelijke
            ExportColumn::make('responsibles.name')
                ->label(__('responsible.model_plural')),

            // subverwerkers
            ExportColumn::make('has_processors')
                ->label(__('avg_processor_processing_record.has_processors')),
            ExportColumn::make('processors.name')
                ->label(__('processor.model_plural')),

            // ontvanger
            ExportColumn::make('receivers.description')
                ->label(__('receiver.model_plural')),

            // doel & grondslag
            ExportColumn::make('has_goal')
                ->label(__('avg_processor_processing_record.has_goal')),
            ExportColumn::make('avgGoals.goal')
                ->label(__('avg_goal.model_plural')),

            // betrokkenen en gegevens
            ExportColumn::make('has_involved')
                ->label(__('avg_processor_processing_record.has_involved')),
            ExportColumn::make('suspects')
                ->label(__('avg_processor_processing_record.suspects')),
            ExportColumn::make('victims')
                ->label(__('avg_processor_processing_record.victims')),
            ExportColumn::make('convicts')
                ->label(__('avg_processor_processing_record.convicts')),
            ExportColumn::make('third_parties')
                ->label(__('avg_processor_processing_record.third_parties')),
            ExportColumn::make('third_parties_description')
                ->label(__('avg_processor_processing_record.third_parties_description')),

            // besluitvorming
            ExportColumn::make('decision_making')
                ->label(__('avg_processor_processing_record.decision_making')),
            ExportColumn::make('logic')
                ->label(__('avg_processor_processing_record.logic')),
            ExportColumn::make('importance_consequences')
                ->label(__('avg_processor_processing_record.importance_consequences')),

            // systemen / applicaties
            ExportColumn::make('has_systems')
                ->label(__('avg_processor_processing_record.has_systems')),
            ExportColumn::make('systems.description')
                ->label(__('system.model_plural')),

            ExportColumn::make('has_algorithms')
                ->label(__('avg_processor_processing_record.has_algorithms')),
            ExportColumn::make('algorithmRecords.name')
                ->label(__('algorithm_record.model_plural')),

            // beveiliging
            ExportColumn::make('has_security')
                ->label(__('avg_processor_processing_record.has_security')),
            ExportColumn::make('measures_implemented')
                ->label(__('processor.measures_implemented')),
            ExportColumn::make('other_measures')
                ->label(__('processor.other_measures')),
            ExportColumn::make('measures_description')
                ->label(__('processor.measures_description')),
            ExportColumn::make('has_pseudonymization')
                ->label(__('avg_processor_processing_record.has_pseudonymization')),
            ExportColumn::make('pseudonymization')
                ->label(__('avg_processor_processing_record.pseudonymization')),

            // doorgifte
            ExportColumn::make('outside_eu')
                ->label(__('avg_processor_processing_record.outside_eu')),
            ExportColumn::make('country')
                ->label(__('general.country')),
            ExportColumn::make('country_other')
                ->label(__('general.country_other')),
            ExportColumn::make('outside_eu_protection_level')
                ->label(__('avg_processor_processing_record.outside_eu_protection_level')),
            ExportColumn::make('outside_eu_protection_level_description')
                ->label(__('avg_processor_processing_record.outside_eu_protection_level_description')),

            // geb (dpia)
            ExportColumn::make('geb_pia')
                ->label(__('avg_processor_processing_record.geb_pia')),

            // contactpersoon
            ExportColumn::make('users.name')
                ->label(__('contact_person.form_title_users')),
            ExportColumn::make('contactPersons.name')
                ->label(__('contact_person.form_title_contact_persons')),

            // opmerkingen
            ExportColumn::make('remarks')
                ->label(__('remark.model_plural')),

            // documenten
            ...self::getDocumentColumns(),

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
