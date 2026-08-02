<?php

declare(strict_types=1);

namespace App\Models\Dpia;

use App\Collections\Avg\AvgResponsibleProcessingRecordCollection;
use App\Collections\DocumentCollection;
use App\Collections\Dpia\DpiaPrescanRecordCollection;
use App\Collections\Dpia\DpiaRecordCollection;
use App\Collections\TagCollection;
use App\Enums\Dpia\PrescanOutcome;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Concerns\HasContactPersons;
use App\Models\Concerns\HasDocuments;
use App\Models\Concerns\HasEntityNumber;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasRemarks;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUsers;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\EntityNumerable;
use App\Models\Contracts\TenantAware;
use Carbon\CarbonImmutable;
use Database\Factories\Dpia\DpiaPrescanRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * A pre-scan DPIA: the threshold assessment recording whether a DPIA (or DTIA,
 * KIA, IAMA) is needed for a given proposal.
 *
 * Kept as a record even when the outcome is "niet verplicht", because the
 * Rijksmodel requires that decision to be documented and archived.
 *
 * @property string $name
 * @property string|null $description
 * @property bool $new_legislation
 * @property bool $departmental_policy
 * @property bool $public_cloud
 * @property array<int, string>|null $ap_criteria
 * @property array<int, string>|null $edpb_criteria
 * @property bool $international_transfer
 * @property bool $outside_eea
 * @property string|null $transfer_mechanism
 * @property bool $digital_service
 * @property bool $minors
 * @property bool $algorithm
 * @property bool $high_risk_ai
 * @property array<int, string>|null $high_risk_ai_categories
 * @property PrescanOutcome|null $outcome
 * @property string|null $outcome_motivation
 * @property CarbonImmutable|null $assessed_at
 *
 * @property-read AvgResponsibleProcessingRecordCollection $avgResponsibleProcessingRecords
 * @property-read DpiaRecordCollection $dpiaRecords
 * @property-read DocumentCollection $documents
 * @property-read TagCollection $tags
 */
class DpiaPrescanRecord extends Model implements EntityNumerable, TenantAware
{
    use HasContactPersons;
    use HasDocuments;
    use HasEntityNumber;
    /** @use HasFactory<DpiaPrescanRecordFactory> */
    use HasFactory;
    use HasOrganisation;
    use HasRemarks;
    use HasSoftDeletes;
    use HasTags;
    use HasTimestamps;
    use HasUsers;
    use HasUuidAsId;

    protected static string $collectionClass = DpiaPrescanRecordCollection::class;

    protected $fillable = [
        'name',
        'description',
        'new_legislation',
        'departmental_policy',
        'public_cloud',
        'ap_criteria',
        'edpb_criteria',
        'international_transfer',
        'outside_eea',
        'transfer_mechanism',
        'digital_service',
        'minors',
        'algorithm',
        'high_risk_ai',
        'high_risk_ai_categories',
        'outcome',
        'outcome_motivation',
        'assessed_at',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'new_legislation' => 'bool',
            'departmental_policy' => 'bool',
            'public_cloud' => 'bool',
            'ap_criteria' => 'array',
            'edpb_criteria' => 'array',
            'international_transfer' => 'bool',
            'outside_eea' => 'bool',
            'digital_service' => 'bool',
            'minors' => 'bool',
            'algorithm' => 'bool',
            'high_risk_ai' => 'bool',
            'high_risk_ai_categories' => 'array',
            'outcome' => PrescanOutcome::class,
            'assessed_at' => 'immutable_date',
        ];
    }

    /**
     * @return HasMany<DpiaRecord, $this>
     */
    public function dpiaRecords(): HasMany
    {
        return $this->hasMany(DpiaRecord::class);
    }

    /**
     * The verwerkingen this pre-scan was carried out for. Carried over to the
     * DPIA when one is started from here.
     *
     * @return MorphToMany<AvgResponsibleProcessingRecord, $this>
     */
    public function avgResponsibleProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgResponsibleProcessingRecord::class, 'dpia_prescan_record_relatable');
    }
}
