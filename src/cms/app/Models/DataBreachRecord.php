<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\Avg\AvgProcessorProcessingRecordCollection;
use App\Collections\Avg\AvgResponsibleProcessingRecordCollection;
use App\Collections\DataBreachRecordCollection;
use App\Collections\DataBreachRecordTransitionCollection;
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
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 *
 * @property-read AvgProcessorProcessingRecordCollection $avgProcessorProcessingRecords
 * @property-read AvgResponsibleProcessingRecordCollection $avgResponsibleProcessingRecords
 * @property-read DataBreachRecordTransitionCollection $dataBreachRecordTransitions
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
        ];
    }

    /**
     * @return HasMany<DataBreachRecordTransition, $this>
     */
    public function dataBreachRecordTransitions(): HasMany
    {
        return $this->hasMany(DataBreachRecordTransition::class)
            ->orderBy('created_at');
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
