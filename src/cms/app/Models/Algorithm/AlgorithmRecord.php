<?php

declare(strict_types=1);

namespace App\Models\Algorithm;

use App\Collections\Algorithm\AlgorithmRecordCollection;
use App\Collections\DocumentCollection;
use App\Components\Uuid\UuidInterface;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasEntityNumber;
use App\Models\Concerns\HasFgRemark;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasSnapshots;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Concerns\IsCloneable;
use App\Models\Contracts\Cloneable;
use App\Models\Contracts\EntityNumerable;
use App\Models\Contracts\SnapshotSource;
use App\Models\Contracts\TenantAware;
use Carbon\CarbonImmutable;
use Database\Factories\Algorithm\AlgorithmRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ?UuidInterface $algorithm_theme_id
 * @property ?UuidInterface $algorithm_status_id
 * @property ?UuidInterface $algorithm_publication_category_id
 * @property ?UuidInterface $algorithm_meta_schema_id
 * @property string|null $import_id
 * @property string $name
 * @property string|null $description
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string|null $contact_data
 * @property string|null $source_link
 * @property string|null $public_page_link
 * @property string|null $resp_goal_and_impact
 * @property string|null $resp_considerations
 * @property string|null $resp_human_intervention
 * @property string|null $resp_risk_analysis
 * @property string|null $resp_legal_base
 * @property string|null $resp_legal_base_link
 * @property string|null $resp_legal_base_title
 * @property string|null $resp_processor_registry_link
 * @property string|null $resp_impact_tests
 * @property string|null $resp_impact_test_links
 * @property string|null $resp_impact_tests_description
 * @property string|null $oper_data
 * @property string|null $oper_links
 * @property string|null $oper_technical_operation
 * @property string|null $oper_data_title
 * @property string|null $oper_supplier
 * @property string|null $oper_source_code_link
 * @property string|null $meta_lang
 * @property string|null $meta_national_id
 * @property string|null $meta_source_id
 * @property string|null $meta_tags
 * @property CarbonImmutable|null $meta_date_of_development
 * @property string|null $meta_owner_algorithm
 * @property string|null $meta_product_owner_algorithm
 * @property bool|null $impact_with_consequences
 * @property bool|null $impact_more_algorithms_applied
 * @property bool|null $impact_effect_on_outcome
 * @property bool|null $validation_answers_checked_by_product_owner
 * @property CarbonImmutable|null $public_from
 *
 * @property-read AlgorithmMetaSchema|null $algorithmMetaSchema
 * @property-read AlgorithmPublicationCategory|null $algorithmPublicationCategory
 * @property-read AlgorithmStatus|null $algorithmStatus
 * @property-read AlgorithmTheme|null $algorithmTheme
 * @property-read DocumentCollection $documents
 */
class AlgorithmRecord extends Model implements Cloneable, EntityNumerable, SnapshotSource, TenantAware
{
    use HasDocuments;
    use HasEntityNumber;
    /** @use HasFactory<AlgorithmRecordFactory> */
    use HasFactory;
    use HasFgRemark;
    use HasOrganisation;
    use HasSnapshots;
    use HasSoftDeletes;
    use HasTimestamps;
    use HasUuidAsId;
    use IsCloneable;

    protected static string $collectionClass = AlgorithmRecordCollection::class;
    protected $fillable = [
        'algorithm_theme_id',
        'algorithm_status_id',
        'algorithm_publication_category_id',
        'algorithm_meta_schema_id',
        'public_from',

        'name',
        'description',
        'start_date',
        'end_date',
        'contact_data',
        'source_link',
        'public_page_link',

        'resp_goal_and_impact',
        'resp_considerations',
        'resp_human_intervention',
        'resp_risk_analysis',
        'resp_legal_base_title',
        'resp_legal_base',
        'resp_legal_base_link',
        'resp_processor_registry_link',
        'resp_impact_tests',
        'resp_impact_test_links',
        'resp_impact_tests_description',

        'oper_data_title',
        'oper_data',
        'oper_links',
        'oper_technical_operation',
        'oper_supplier',
        'oper_source_code_link',

        'meta_lang',
        'meta_national_id',
        'meta_source_id',
        'meta_tags',
        'meta_date_of_development',
        'meta_owner_algorithm',
        'meta_product_owner_algorithm',

        'impact_with_consequences',
        'impact_more_algorithms_applied',
        'impact_effect_on_outcome',
        'validation_answers_checked_by_product_owner',
    ];

    public function casts(): array
    {
        return [
            'algorithm_theme_id' => UuidCast::class,
            'algorithm_status_id' => UuidCast::class,
            'algorithm_publication_category_id' => UuidCast::class,
            'algorithm_meta_schema_id' => UuidCast::class,
            'public_from' => 'datetime',
            'meta_date_of_development' => 'date',
            'impact_with_consequences' => 'boolean',
            'impact_more_algorithms_applied' => 'boolean',
            'impact_effect_on_outcome' => 'boolean',
            'validation_answers_checked_by_product_owner' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<AlgorithmTheme, $this>
     */
    public function algorithmTheme(): BelongsTo
    {
        return $this->belongsTo(AlgorithmTheme::class, 'algorithm_theme_id');
    }

    /**
     * @return BelongsTo<AlgorithmStatus, $this>
     */
    public function algorithmStatus(): BelongsTo
    {
        return $this->belongsTo(AlgorithmStatus::class, 'algorithm_status_id');
    }

    /**
     * @return BelongsTo<AlgorithmPublicationCategory, $this>
     */
    public function algorithmPublicationCategory(): BelongsTo
    {
        return $this->belongsTo(AlgorithmPublicationCategory::class, 'algorithm_publication_category_id');
    }

    /**
     * @return BelongsTo<AlgorithmMetaSchema, $this>
     */
    public function algorithmMetaSchema(): BelongsTo
    {
        return $this->belongsTo(AlgorithmMetaSchema::class, 'algorithm_meta_schema_id');
    }
}
