<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\Avg\AvgProcessorProcessingRecordCollection;
use App\Collections\Avg\AvgResponsibleProcessingRecordCollection;
use App\Collections\DataBreachRecordCollection;
use App\Collections\DocumentCollection;
use App\Collections\ResponsibleCollection;
use App\Collections\Wpg\WpgProcessingRecordCollection;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasEntityNumber;
use App\Models\Concerns\HasFgRemark;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasResponsibles;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\EntityNumerable;
use App\Models\Contracts\TenantAware;
use App\Models\States\DataBreachRecordState;
use App\Models\Wpg\WpgProcessingRecord;
use Carbon\CarbonImmutable;
use Database\Factories\DataBreachRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\ModelStates\HasStates;
use Spatie\ModelStates\HasStatesContract;

/**
 * @property string $name
 * @property string $type
 * @property DataBreachRecordState $state
 * @property CarbonImmutable|null $reported_at
 * @property bool $ap_reported
 * @property CarbonImmutable|null $discovered_at
 * @property CarbonImmutable|null $started_at
 * @property CarbonImmutable|null $ended_at
 * @property CarbonImmutable|null $ap_reported_at
 * @property CarbonImmutable|null $completed_at
 * @property string|null $nature_of_incident
 * @property string|null $nature_of_incident_other
 * @property string|null $summary
 * @property string|null $involved_people
 * @property array<string>|null $personal_data_categories
 * @property string|null $personal_data_categories_other
 * @property array<string>|null $personal_data_special_categories
 * @property string|null $estimated_risk
 * @property string|null $measures
 * @property bool $reported_to_involved
 * @property array<string>|null $reported_to_involved_communication
 * @property string|null $reported_to_involved_communication_other
 * @property bool $fg_reported
 * @property array<string>|null $other_supervisors
 * @property string|null $other_supervisors_other
 * @property bool $cross_border
 * @property string|null $cross_border_countries
 * @property string|null $reported_other_dpas
 * @property string|null $how_discovered
 * @property string|null $late_notification_reason
 * @property array<string>|null $nature_of_breach
 * @property string|null $record_count
 * @property string|null $record_count_explanation
 * @property array<string>|null $affected_groups
 * @property string|null $affected_groups_other
 * @property bool $affected_count_known
 * @property int|null $affected_count
 * @property int|null $affected_count_min
 * @property int|null $affected_count_max
 * @property array<string>|null $protection_beforehand
 * @property string|null $protection_beforehand_explanation
 * @property array<string>|null $consequences_controller
 * @property string|null $consequences_controller_other
 * @property array<string>|null $consequences_data_subjects
 * @property string|null $consequences_data_subjects_other
 * @property string|null $risk_severity
 * @property int|null $reported_to_involved_count
 *
 * @property-read AvgProcessorProcessingRecordCollection $avgProcessorProcessingRecords
 * @property-read AvgResponsibleProcessingRecordCollection $avgResponsibleProcessingRecords
 * @property-read DocumentCollection $documents
 * @property-read ResponsibleCollection $responsibles
 * @property-read WpgProcessingRecordCollection $wpgProcessingRecords
 */
class DataBreachRecord extends Model implements EntityNumerable, HasStatesContract, TenantAware
{
    use HasDocuments;
    use HasEntityNumber;
    /** @use HasFactory<DataBreachRecordFactory> */
    use HasFactory;
    use HasFgRemark;
    use HasOrganisation;
    use HasResponsibles;
    use HasSoftDeletes;
    use HasStates;
    use HasTags;
    use HasTimestamps;
    use HasUuidAsId;

    protected static string $collectionClass = DataBreachRecordCollection::class;
    protected $fillable = [
        'name',
        'type',
        'state',
        'reported_at',
        'ap_reported',

        'discovered_at',
        'started_at',
        'ended_at',
        'ap_reported_at',
        'completed_at',

        'nature_of_incident',
        'nature_of_incident_other',
        'summary',
        'involved_people',
        'personal_data_categories',
        'personal_data_categories_other',
        'personal_data_special_categories',
        'estimated_risk',
        'measures',
        'reported_to_involved',
        'reported_to_involved_communication',
        'reported_to_involved_communication_other',
        'fg_reported',

        'other_supervisors',
        'other_supervisors_other',
        'cross_border',
        'cross_border_countries',
        'reported_other_dpas',
        'how_discovered',
        'late_notification_reason',
        'nature_of_breach',
        'record_count',
        'record_count_explanation',
        'affected_groups',
        'affected_groups_other',
        'affected_count_known',
        'affected_count',
        'affected_count_min',
        'affected_count_max',
        'protection_beforehand',
        'protection_beforehand_explanation',
        'consequences_controller',
        'consequences_controller_other',
        'consequences_data_subjects',
        'consequences_data_subjects_other',
        'risk_severity',
        'reported_to_involved_count',
    ];

    public function casts(): array
    {
        return [
            'ap_reported' => 'boolean',
            'state' => DataBreachRecordState::class,

            'reported_at' => 'date',
            'discovered_at' => 'date',
            'started_at' => 'date',
            'ended_at' => 'date',
            'ap_reported_at' => 'date',
            'completed_at' => 'date',

            'personal_data_categories' => 'array',
            'personal_data_special_categories' => 'array',
            'reported_to_involved_communication' => 'array',

            'cross_border' => 'boolean',
            'affected_count_known' => 'boolean',
            'affected_count' => 'integer',
            'affected_count_min' => 'integer',
            'affected_count_max' => 'integer',
            'reported_to_involved_count' => 'integer',

            'other_supervisors' => 'array',
            'nature_of_breach' => 'array',
            'affected_groups' => 'array',
            'protection_beforehand' => 'array',
            'consequences_controller' => 'array',
            'consequences_data_subjects' => 'array',
        ];
    }

    /**
     * @return MorphToMany<AvgProcessorProcessingRecord, $this>
     */
    public function avgProcessorProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgProcessorProcessingRecord::class, 'data_breach_record_relatable');
    }

    /**
     * @return MorphToMany<AvgResponsibleProcessingRecord, $this>
     */
    public function avgResponsibleProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgResponsibleProcessingRecord::class, 'data_breach_record_relatable');
    }

    /**
     * @return MorphToMany<WpgProcessingRecord, $this>
     */
    public function wpgProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(WpgProcessingRecord::class, 'data_breach_record_relatable');
    }
}
