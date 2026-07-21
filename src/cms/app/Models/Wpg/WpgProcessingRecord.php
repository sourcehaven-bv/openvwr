<?php

declare(strict_types=1);

namespace App\Models\Wpg;

use App\Collections\DataBreachRecordCollection;
use App\Collections\DocumentCollection;
use App\Collections\ProcessorCollection;
use App\Collections\ResponsibleCollection;
use App\Collections\SystemCollection;
use App\Collections\Wpg\WpgGoalCollection;
use App\Collections\Wpg\WpgProcessingRecordCollection;
use App\Components\Uuid\UuidInterface;
use App\Enums\CoreEntityDataCollectionSource;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasChildren;
use App\Models\Concerns\HasContactPersons;
use App\Models\Concerns\HasDataBreachRecords;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasEntityNumber;
use App\Models\Concerns\HasFgRemark;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasProcessors;
use App\Models\Concerns\HasRemarks;
use App\Models\Concerns\HasResponsibles;
use App\Models\Concerns\HasSnapshots;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasSystems;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUsers;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Concerns\HasWpgGoals;
use App\Models\Concerns\IsCloneable;
use App\Models\Concerns\IsReviewable;
use App\Models\Contracts\Cloneable;
use App\Models\Contracts\EntityNumerable;
use App\Models\Contracts\Reviewable;
use App\Models\Contracts\SnapshotSource;
use App\Models\Contracts\TenantAware;
use Carbon\CarbonImmutable;
use Database\Factories\Wpg\WpgProcessingRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $name
 * @property string|null $import_id
 * @property string|null $import_number
 * @property bool $suspects
 * @property bool $victims
 * @property bool $convicts
 * @property bool $police_justice
 * @property bool $third_parties
 * @property string|null $third_party_explanation
 * @property bool $has_security
 * @property bool $decision_making
 * @property bool $article_15
 * @property bool $article_15_a
 * @property bool $article_16
 * @property bool $article_17
 * @property bool $article_17_a
 * @property bool $article_18
 * @property bool $article_19
 * @property bool $article_20
 * @property bool $article_22
 * @property bool $article_23
 * @property bool $article_24
 * @property bool $geb_pia
 * @property string|null $explanation_available
 * @property string|null $explanation_provisioning
 * @property string|null $explanation_transfer
 * @property string|null $logic
 * @property string|null $consequences
 * @property string|null $pseudonymization
 * @property bool $measures_implemented
 * @property bool $other_measures
 * @property string|null $measures_description
 * @property ?UuidInterface $wpg_processing_record_service_id
 * @property CarbonImmutable|null $public_from
 * @property bool $has_processors
 * @property bool $police_race_or_ethnicity
 * @property bool $police_political_attitude
 * @property bool $police_faith_or_belief
 * @property bool $police_association_membership
 * @property bool $police_genetic
 * @property bool $police_identification
 * @property bool $police_health
 * @property bool $police_sexual_life
 * @property CoreEntityDataCollectionSource $data_collection_source
 * @property bool $has_systems
 * @property bool $has_pseudonymization
 *
 * @property-read DataBreachRecordCollection $dataBreachRecords
 * @property-read DocumentCollection $documents
 * @property-read ProcessorCollection $processors
 * @property-read ResponsibleCollection $responsibles
 * @property-read SystemCollection $systems
 * @property-read WpgGoalCollection $wpgGoals
 * @property-read WpgProcessingRecordService|null $wpgProcessingRecordService
 */
class WpgProcessingRecord extends Model implements Cloneable, EntityNumerable, Reviewable, SnapshotSource, TenantAware
{
    /** @use HasChildren<WpgProcessingRecord, WpgProcessingRecordCollection> */
    use HasChildren;
    use HasContactPersons;
    use HasDataBreachRecords;
    use HasDocuments;
    use HasEntityNumber;
    /** @use HasFactory<WpgProcessingRecordFactory> */
    use HasFactory;
    use HasFgRemark;
    use HasOrganisation;
    use HasProcessors;
    use HasRemarks;
    use HasResponsibles;
    use HasSnapshots;
    use HasSoftDeletes;
    use HasSystems;
    use HasTags;
    use HasTimestamps;
    use HasUuidAsId;
    use HasUsers;
    use HasWpgGoals;
    use IsCloneable;
    use IsReviewable;

    protected static string $collectionClass = WpgProcessingRecordCollection::class;
    protected $fillable = [
        'system_id',
        'wpg_processing_record_service_id',
        'receiver_id',

        'data_collection_source',
        'name',
        'service',
        'suspects',
        'victims',
        'convicts',
        'police_justice',
        'third_parties',
        'third_party_explanation',
        'has_pseudonymization',
        'pseudonymization',
        'measures_implemented',
        'other_measures',
        'measures_description',
        'has_security',
        'decision_making',
        'logic',
        'consequences',
        'article_15',
        'article_15_a',
        'article_16',
        'article_17',
        'article_17_a',
        'article_18',
        'article_19',
        'article_20',
        'article_22',
        'article_23',
        'article_24',
        'explanation_available',
        'explanation_provisioning',
        'explanation_transfer',
        'geb_pia',
        'import_id',
        'created_at',
        'updated_at',
        'public_from',
        'has_processors',
        'has_systems',
        'police_race_or_ethnicity',
        'police_political_attitude',
        'police_faith_or_belief',
        'police_association_membership',
        'police_genetic',
        'police_identification',
        'police_health',
        'police_sexual_life',
    ];

    public function casts(): array
    {
        return [
            'article_15' => 'bool',
            'article_15_a' => 'bool',
            'article_16' => 'bool',
            'article_17' => 'bool',
            'article_17_a' => 'bool',
            'article_18' => 'bool',
            'article_19' => 'bool',
            'article_20' => 'bool',
            'article_22' => 'bool',
            'article_23' => 'bool',
            'article_24' => 'bool',
            'decision_making' => 'bool',
            'convicts' => 'bool',
            'geb_pia' => 'bool',
            'has_security' => 'bool',
            'suspects' => 'bool',
            'police_justice' => 'bool',
            'third_parties' => 'bool',
            'victims' => 'bool',
            'wpg_processing_record_service_id' => UuidCast::class,
            'public_from' => 'datetime',
            'has_processors' => 'bool',
            'has_systems' => 'bool',
            'police_race_or_ethnicity' => 'bool',
            'police_political_attitude' => 'bool',
            'police_faith_or_belief' => 'bool',
            'police_association_membership' => 'bool',
            'police_genetic' => 'bool',
            'police_identification' => 'bool',
            'police_health' => 'bool',
            'police_sexual_life' => 'bool',
            'data_collection_source' => CoreEntityDataCollectionSource::class,
            'measures' => 'bool',
            'other_measures' => 'bool',
        ];
    }

    /**
     * @return BelongsTo<WpgProcessingRecordService, $this>
     */
    public function wpgProcessingRecordService(): BelongsTo
    {
        return $this->belongsTo(WpgProcessingRecordService::class);
    }
}
