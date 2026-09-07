<?php

declare(strict_types=1);

namespace App\Import\Factories;

use App\Components\Uuid\UuidInterface;
use App\Import\Factories\Concerns\DataConverters;
use App\Import\Factory;
use App\Models\DataBreachRecord;

/**
 * Imports data breach records (datalekken).
 *
 * Unlike the processing records this model carries no snapshot or workflow
 * state, so an imported record enters as a plain source record and is taken
 * through review by hand. See docs/import_mapping_design.md.
 *
 * @implements Factory<DataBreachRecord>
 */
class DataBreachRecordFactory implements Factory
{
    use DataConverters;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data, UuidInterface $organisationId): ?DataBreachRecord
    {
        /** @var DataBreachRecord $dataBreachRecord */
        $dataBreachRecord = DataBreachRecord::firstOrNew([
            'import_id' => $data['Id'] ?? null,
            'organisation_id' => $organisationId,
        ]);

        if ($dataBreachRecord->exists) {
            return null;
        }

        $dataBreachRecord->organisation_id = $organisationId;
        $dataBreachRecord->import_id = $this->toStringOrNull($data, 'Id');

        $dataBreachRecord->name = $this->toString($data, 'Naam');
        $dataBreachRecord->type = $this->toString($data, 'Type');

        $dataBreachRecord->reported_at = $this->toCalendarDateOrNull($data, 'DatumMelding');
        $dataBreachRecord->discovered_at = $this->toCalendarDateOrNull($data, 'DatumOntdekking');
        $dataBreachRecord->started_at = $this->toCalendarDateOrNull($data, 'StartdatumInbreuk');
        $dataBreachRecord->ended_at = $this->toCalendarDateOrNull($data, 'EinddatumInbreuk');
        $dataBreachRecord->ap_reported_at = $this->toCalendarDateOrNull($data, 'DatumMeldingAp');
        $dataBreachRecord->completed_at = $this->toCalendarDateOrNull($data, 'DatumAfronding');

        $dataBreachRecord->ap_reported = $this->toBoolean($data, 'GemeldAp');
        $dataBreachRecord->fg_reported = $this->toBoolean($data, 'GemeldFg');
        $dataBreachRecord->reported_to_involved = $this->toBoolean($data, 'GemeldBetrokkene');

        $dataBreachRecord->nature_of_incident = $this->toStringOrNull($data, 'AardIncident');
        $dataBreachRecord->nature_of_incident_other = $this->toStringOrNull($data, 'AardIncidentAnders');
        $dataBreachRecord->summary = $this->toStringOrNull($data, 'Samenvatting');
        $dataBreachRecord->involved_people = $this->toStringOrNull($data, 'BetrokkenPersonen');
        $dataBreachRecord->estimated_risk = $this->toStringOrNull($data, 'InschattingRisico');
        $dataBreachRecord->measures = $this->toStringOrNull($data, 'Maatregelen');

        $dataBreachRecord->personal_data_categories = $this->toStringList($data, 'CategorieenPersoonsgegevens');
        $dataBreachRecord->personal_data_categories_other = $this->toStringOrNull($data, 'CategorieenPersoonsgegevensAnders');
        $dataBreachRecord->personal_data_special_categories = $this->toStringList($data, 'BijzondereCategorieen');
        $dataBreachRecord->reported_to_involved_communication = $this->toStringList($data, 'CommunicatiemiddelBetrokkene');
        $dataBreachRecord->reported_to_involved_communication_other = $this->toStringOrNull($data, 'CommunicatiemiddelBetrokkeneAnders');

        $dataBreachRecord->save();

        return $dataBreachRecord;
    }
}
