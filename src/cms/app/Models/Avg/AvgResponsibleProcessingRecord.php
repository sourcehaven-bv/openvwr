<?php

declare(strict_types=1);

namespace App\Models\Avg;

use App\Collections\Avg\AvgResponsibleProcessingRecordCollection;
use App\Collections\DataBreachRecordCollection;
use App\Collections\DocumentCollection;
use App\Collections\Dpia\DpiaRecordCollection;
use App\Collections\ProcessorCollection;
use App\Collections\ReceiverCollection;
use App\Collections\ResponsibleCollection;
use App\Collections\SystemCollection;
use App\Collections\TagCollection;
use App\Components\Uuid\UuidInterface;
use App\Enums\CoreEntityDataCollectionSource;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasAlgorithmRecords;
use App\Models\Concerns\HasAvgGoals;
use App\Models\Concerns\HasChildren;
use App\Models\Concerns\HasContactPersons;
use App\Models\Concerns\HasDataBreachRecords;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasDpiaRecords;
use App\Models\Concerns\HasEntityNumber;
use App\Models\Concerns\HasFgRemark;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasProcessors;
use App\Models\Concerns\HasReceivers;
use App\Models\Concerns\HasRemarks;
use App\Models\Concerns\HasResponsibles;
use App\Models\Concerns\HasSnapshots;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasStakeholders;
use App\Models\Concerns\HasSystems;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUsers;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Concerns\IsCloneable;
use App\Models\Concerns\IsPublishable;
use App\Models\Concerns\IsReviewable;
use App\Models\Contracts\Cloneable;
use App\Models\Contracts\EntityNumerable;
use App\Models\Contracts\Publishable;
use App\Models\Contracts\Reviewable;
use App\Models\Contracts\TenantAware;
use Database\Factories\Avg\AvgResponsibleProcessingRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $name
 * @property bool $outside_eu
 * @property bool $decision_making
 * @property string|null $import_id
 * @property string|null $import_number
 * @property bool $has_processors
 * @property bool $has_security
 * @property bool $has_systems
 * @property bool $has_algorithms
 * @property string|null $responsibility_distribution
 * @property string|null $pseudonymization
 * @property bool $measures_implemented
 * @property bool $other_measures
 * @property string|null $outside_eu_description
 * @property string|null $outside_eu_protection_level_description
 * @property ?UuidInterface $avg_responsible_processing_record_service_id
 * @property bool $has_pseudonymization
 * @property string|null $logic
 * @property string|null $importance_consequences
 * @property string|null $country
 * @property string|null $country_other
 * @property bool $outside_eu_protection_level
 * @property bool $geb_dpia_executed
 * @property bool $geb_dpia_automated
 * @property bool $geb_dpia_large_scale_processing
 * @property bool $geb_dpia_large_scale_monitoring
 * @property bool $geb_dpia_list_required
 * @property bool $geb_dpia_criteria_wp248
 * @property bool $geb_dpia_high_risk_freedoms
 * @property string|null $measures_description
 * @property CoreEntityDataCollectionSource $data_collection_source
 * @property string|null $published_at
 *
 * @property-read AvgResponsibleProcessingRecordService|null $avgResponsibleProcessingRecordService
 * @property-read DataBreachRecordCollection $dataBreachRecords
 * @property-read DocumentCollection $documents
 * @property-read DpiaRecordCollection $dpiaRecords
 * @property-read ProcessorCollection $processors
 * @property-read ReceiverCollection $receivers
 * @property-read ResponsibleCollection $responsibles
 * @property-read SystemCollection $systems
 * @property-read TagCollection $tags
 */
class AvgResponsibleProcessingRecord extends Model implements Cloneable, EntityNumerable, Publishable, Reviewable, TenantAware
{
    use HasAlgorithmRecords;
    use HasAvgGoals;
    /** @use HasChildren<AvgResponsibleProcessingRecord, AvgResponsibleProcessingRecordCollection> */
    use HasChildren;
    use HasContactPersons;
    use HasDataBreachRecords;
    use HasDocuments;
    use HasDpiaRecords;
    use HasEntityNumber;
    /** @use HasFactory<AvgResponsibleProcessingRecordFactory> */
    use HasFactory;
    use HasFgRemark;
    use HasOrganisation;
    use HasProcessors;
    use HasReceivers;
    use HasRemarks;
    use HasResponsibles;
    use HasSnapshots;
    use HasStakeholders;
    use HasSoftDeletes;
    use HasSystems;
    use HasTags;
    use HasTimestamps;
    use HasUsers;
    use HasUuidAsId;
    use IsCloneable;
    use IsPublishable;
    use IsReviewable;

    protected static string $collectionClass = AvgResponsibleProcessingRecordCollection::class;
    protected $fillable = [
        'avg_responsible_processing_record_service_id',

        'data_collection_source',
        'name',
        'service',
        'responsibility_distribution',
        'has_pseudonymization',
        'pseudonymization',
        'measures_implemented',
        'other_measures',
        'measures_description',
        'outside_eu',
        'outside_eu_description',
        'outside_eu_protection_level',
        'outside_eu_protection_level_description',
        'country',
        'country_other',
        'decision_making',
        'logic',
        'importance_consequences',
        'import_id',
        'has_processors',
        'has_security',
        'has_systems',
        'has_algorithms',
        'geb_dpia_executed',
        'geb_dpia_automated',
        'geb_dpia_large_scale_processing',
        'geb_dpia_large_scale_monitoring',
        'geb_dpia_list_required',
        'geb_dpia_criteria_wp248',
        'geb_dpia_high_risk_freedoms',
        'public_from',
    ];

    public function casts(): array
    {
        return [
            'avg_responsible_processing_record_service_id' => UuidCast::class,
            'decision_making' => 'bool',
            'has_processors' => 'bool',
            'has_security' => 'bool',
            'has_systems' => 'bool',
            'has_algorithms' => 'bool',
            'measures' => 'bool',
            'other_measures' => 'bool',
            'outside_eu' => 'bool',
            'has_pseudonymization' => 'bool',
            'outside_eu_protection_level' => 'bool',
            'geb_dpia_executed' => 'bool',
            'geb_dpia_automated' => 'bool',
            'geb_dpia_large_scale_processing' => 'bool',
            'geb_dpia_large_scale_monitoring' => 'bool',
            'geb_dpia_list_required' => 'bool',
            'geb_dpia_criteria_wp248' => 'bool',
            'geb_dpia_high_risk_freedoms' => 'bool',
            'public_from' => 'datetime',
            'data_collection_source' => CoreEntityDataCollectionSource::class,
        ];
    }

    /**
     * @return BelongsTo<AvgResponsibleProcessingRecordService, $this>
     */
    public function avgResponsibleProcessingRecordService(): BelongsTo
    {
        return $this->belongsTo(AvgResponsibleProcessingRecordService::class);
    }
}
