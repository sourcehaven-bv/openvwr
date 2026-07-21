<?php

declare(strict_types=1);

namespace App\Models\Avg;

use App\Collections\Avg\AvgProcessorProcessingRecordCollection;
use App\Collections\DataBreachRecordCollection;
use App\Collections\DocumentCollection;
use App\Collections\ProcessorCollection;
use App\Collections\ReceiverCollection;
use App\Collections\ResponsibleCollection;
use App\Collections\SystemCollection;
use App\Components\Uuid\UuidInterface;
use App\Enums\CoreEntityDataCollectionSource;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasAvgGoals;
use App\Models\Concerns\HasChildren;
use App\Models\Concerns\HasContactPersons;
use App\Models\Concerns\HasDataBreachRecords;
use App\Models\Concerns\HasDocuments;
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
use App\Models\Concerns\IsReviewable;
use App\Models\Contracts\Cloneable;
use App\Models\Contracts\EntityNumerable;
use App\Models\Contracts\Reviewable;
use App\Models\Contracts\SnapshotSource;
use App\Models\Contracts\TenantAware;
use Carbon\CarbonImmutable;
use Database\Factories\Avg\AvgProcessorProcessingRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $name
 * @property bool $outside_eu
 * @property string|null $country
 * @property string|null $country_other
 * @property bool $decision_making
 * @property string|null $import_id
 * @property string|null $import_number
 * @property bool $outside_eu_protection_level
 * @property ?UuidInterface $avg_processor_processing_record_service_id
 * @property bool $has_processors
 * @property bool $has_pseudonymization
 * @property bool $has_goal
 * @property bool $has_involved
 * @property bool $suspects
 * @property bool $victims
 * @property bool $convicts
 * @property bool $third_parties
 * @property string $third_parties_description
 * @property bool $has_security
 * @property string|null $arrangements_with_responsibles_description
 * @property string|null $arrangements_with_processors_description
 * @property bool $measures_implemented
 * @property bool $other_measures
 * @property string|null $measures_description
 * @property bool $geb_pia
 * @property string $pseudonymization
 * @property string $responsibility_distribution
 * @property string $outside_eu_protection_level_description
 * @property string|null $outside_eu_description
 * @property string $logic
 * @property string $importance_consequences
 * @property CarbonImmutable|null $public_from
 * @property CoreEntityDataCollectionSource $data_collection_source
 * @property bool $has_systems
 *
 * @property-read AvgProcessorProcessingRecordService|null $avgProcessorProcessingRecordService
 * @property-read DataBreachRecordCollection $dataBreachRecords
 * @property-read DocumentCollection $documents
 * @property-read ProcessorCollection $processors
 * @property-read ReceiverCollection $receivers
 * @property-read ResponsibleCollection $responsibles
 * @property-read SystemCollection $systems
 */
class AvgProcessorProcessingRecord extends Model implements Cloneable, EntityNumerable, Reviewable, SnapshotSource, TenantAware
{
    use HasAvgGoals;
    /** @use HasChildren<AvgProcessorProcessingRecord, AvgProcessorProcessingRecordCollection> */
    use HasChildren;
    use HasContactPersons;
    use HasDataBreachRecords;
    use HasDocuments;
    use HasEntityNumber;
    /** @use HasFactory<AvgProcessorProcessingRecordFactory> */
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
    use IsReviewable;

    protected static string $collectionClass = AvgProcessorProcessingRecordCollection::class;
    protected $fillable = [
        'avg_processor_processing_record_service_id',

        'data_collection_source',
        'name',
        'responsibility_distribution',
        'pseudonymization',
        'access',
        'safety_processors',
        'safety_responsibles',
        'measures_implemented',
        'other_measures',
        'measures_description',
        'outside_eu',
        'country',
        'country_other',
        'outside_eu_protection_level',
        'outside_eu_protection_level_description',
        'outside_eu_description',
        'decision_making',
        'logic',
        'importance_consequences',
        'geb_pia',
        'import_id',
        'has_processors',
        'has_goal',
        'has_security',
        'has_pseudonymization',
        'has_involved',
        'suspects',
        'victims',
        'convicts',
        'public_from',
        'has_systems',
    ];

    public function casts(): array
    {
        return [
            'avg_processor_processing_record_service_id' => UuidCast::class,
            'convicts' => 'bool',
            'decision_making' => 'bool',
            'has_goal' => 'bool',
            'has_involved' => 'bool',
            'has_pseudonymization' => 'bool',
            'has_security' => 'bool',
            'has_processors' => 'bool',
            'measures_implemented' => 'bool',
            'other_measures' => 'bool',
            'outside_eu' => 'bool',
            'outside_eu_protection_level' => 'bool',
            'suspects' => 'bool',
            'third_parties' => 'bool',
            'victims' => 'bool',
            'geb_pia' => 'bool',
            'public_from' => 'datetime',
            'has_systems' => 'bool',
            'data_collection_source' => CoreEntityDataCollectionSource::class,
        ];
    }

    /**
     * @return BelongsTo<AvgProcessorProcessingRecordService, $this>
     */
    public function avgProcessorProcessingRecordService(): BelongsTo
    {
        return $this->belongsTo(AvgProcessorProcessingRecordService::class);
    }
}
