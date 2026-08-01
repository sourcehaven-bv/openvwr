<?php

declare(strict_types=1);

namespace App\Models\Dpia;

use App\Collections\Avg\AvgResponsibleProcessingRecordCollection;
use App\Collections\DocumentCollection;
use App\Collections\Dpia\DpiaMeasureCollection;
use App\Collections\Dpia\DpiaPersonalDataCollection;
use App\Collections\Dpia\DpiaRecordCollection;
use App\Collections\Dpia\DpiaRiskCollection;
use App\Collections\ProcessorCollection;
use App\Collections\ResponsibleCollection;
use App\Collections\SystemCollection;
use App\Collections\TagCollection;
use App\Components\Uuid\UuidInterface;
use App\Enums\Dpia\DpiaSubjectType;
use App\Enums\Dpia\RiskLevel;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasContactPersons;
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
use App\Models\Concerns\IsCloneable;
use App\Models\Concerns\IsReviewable;
use App\Models\Contracts\Cloneable;
use App\Models\Contracts\EntityNumerable;
use App\Models\Contracts\Reviewable;
use App\Models\Contracts\SnapshotSource;
use App\Models\Contracts\TenantAware;
use Carbon\CarbonImmutable;
use Database\Factories\Dpia\DpiaRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * A gegevensbeschermingseffectbeoordeling following Model DPIA Rijksdienst
 * v3.0. The columns map one-to-one onto the 17 paragraphs of that model; see
 * the migration for the paragraph numbers.
 *
 * Note that a DPIA is deliberately not publishable to the static website. The
 * Woo does not list DPIAs among the categories for active disclosure and they
 * routinely contain security measures and residual risks that should not be
 * public by default. Publication stays a conscious, separate decision.
 *
 * @property string $name
 * @property DpiaSubjectType $subject_type
 * @property string|null $proposal_description
 * @property string|null $proposal_motivation
 * @property string|null $personal_data_description
 * @property string|null $personal_data_sources
 * @property string|null $processing_description
 * @property string|null $techniques_description
 * @property bool $automated_decision_making
 * @property bool $profiling
 * @property bool $cloud_processing
 * @property bool $big_data_processing
 * @property string|null $techniques_explanation
 * @property string|null $purpose_description
 * @property string|null $parties_description
 * @property string|null $parties_access
 * @property string|null $interests_description
 * @property string|null $interests_data_subjects
 * @property string|null $processing_locations
 * @property bool $outside_eea
 * @property string|null $transfer_mechanism
 * @property string|null $transfer_safeguards
 * @property string|null $legal_policy_framework
 * @property string|null $retention_periods
 * @property string|null $retention_motivation
 * @property string|null $retention_responsible
 * @property string|null $legal_basis
 * @property string|null $legal_basis_conditions
 * @property bool $special_categories
 * @property string|null $special_categories_exception
 * @property bool $national_identification_number
 * @property string|null $national_identification_number_basis
 * @property bool $further_processing
 * @property string|null $purpose_limitation
 * @property string|null $necessity_proportionality
 * @property string|null $necessity_subsidiarity
 * @property string|null $data_subject_rights_procedure
 * @property bool $rights_restricted
 * @property string|null $rights_restriction_basis
 * @property string|null $risks_additional_information
 * @property string|null $measures_additional_information
 * @property string|null $residual_risk_acceptance
 * @property bool $data_subjects_consulted
 * @property string|null $data_subjects_consultation
 * @property string|null $fg_advice
 * @property string|null $fg_advice_followup
 * @property CarbonImmutable|null $fg_advice_received_at
 * @property bool $ap_consultation_required
 * @property string|null $ap_consultation
 * @property CarbonImmutable|null $ap_consultation_requested_at
 * @property CarbonImmutable|null $assessed_at
 * @property string|null $management_summary
 * @property string|null $import_id
 * @property string|null $import_number
 * @property UuidInterface|null $dpia_prescan_record_id
 *
 * @property-read DpiaPrescanRecord|null $dpiaPrescanRecord
 * @property-read AvgResponsibleProcessingRecordCollection $avgResponsibleProcessingRecords
 * @property-read DpiaPersonalDataCollection $personalData
 * @property-read DpiaRiskCollection $risks
 * @property-read DpiaMeasureCollection $measures
 * @property-read DocumentCollection $documents
 * @property-read ProcessorCollection $processors
 * @property-read ResponsibleCollection $responsibles
 * @property-read SystemCollection $systems
 * @property-read TagCollection $tags
 */
class DpiaRecord extends Model implements Cloneable, EntityNumerable, Reviewable, SnapshotSource, TenantAware
{
    use HasContactPersons;
    use HasDocuments;
    use HasEntityNumber;
    /** @use HasFactory<DpiaRecordFactory> */
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
    use IsCloneable;
    use IsReviewable;

    protected static string $collectionClass = DpiaRecordCollection::class;

    protected $fillable = [
        'dpia_prescan_record_id',
        'name',
        'subject_type',

        'proposal_description',
        'proposal_motivation',
        'personal_data_description',
        'personal_data_sources',
        'processing_description',
        'techniques_description',
        'automated_decision_making',
        'profiling',
        'cloud_processing',
        'big_data_processing',
        'techniques_explanation',
        'purpose_description',
        'parties_description',
        'parties_access',
        'interests_description',
        'interests_data_subjects',
        'processing_locations',
        'outside_eea',
        'transfer_mechanism',
        'transfer_safeguards',
        'legal_policy_framework',
        'retention_periods',
        'retention_motivation',
        'retention_responsible',

        'legal_basis',
        'legal_basis_conditions',
        'special_categories',
        'special_categories_exception',
        'national_identification_number',
        'national_identification_number_basis',
        'further_processing',
        'purpose_limitation',
        'necessity_proportionality',
        'necessity_subsidiarity',
        'data_subject_rights_procedure',
        'rights_restricted',
        'rights_restriction_basis',

        'risks_additional_information',
        'measures_additional_information',
        'residual_risk_acceptance',

        'data_subjects_consulted',
        'data_subjects_consultation',
        'fg_advice',
        'fg_advice_followup',
        'fg_advice_received_at',
        'ap_consultation_required',
        'ap_consultation',
        'ap_consultation_requested_at',

        'assessed_at',
        'management_summary',
        'import_id',
        'import_number',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'dpia_prescan_record_id' => UuidCast::class,
            'subject_type' => DpiaSubjectType::class,
            'automated_decision_making' => 'bool',
            'profiling' => 'bool',
            'cloud_processing' => 'bool',
            'big_data_processing' => 'bool',
            'outside_eea' => 'bool',
            'special_categories' => 'bool',
            'national_identification_number' => 'bool',
            'further_processing' => 'bool',
            'rights_restricted' => 'bool',
            'data_subjects_consulted' => 'bool',
            'ap_consultation_required' => 'bool',
            'fg_advice_received_at' => 'immutable_date',
            'ap_consultation_requested_at' => 'immutable_date',
            'assessed_at' => 'immutable_date',
        ];
    }

    /**
     * @return BelongsTo<DpiaPrescanRecord, $this>
     */
    public function dpiaPrescanRecord(): BelongsTo
    {
        return $this->belongsTo(DpiaPrescanRecord::class);
    }

    /**
     * The verwerkingen this DPIA covers. One DPIA may cover a series of
     * comparable processing operations, so this is many-to-many.
     *
     * @return MorphToMany<AvgResponsibleProcessingRecord, $this>
     */
    public function avgResponsibleProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgResponsibleProcessingRecord::class, 'dpia_record_relatable');
    }

    /**
     * The personal data of paragraaf 2, which paragraaf 12 leans on.
     *
     * @return HasMany<DpiaPersonalData, $this>
     */
    public function personalData(): HasMany
    {
        return $this->hasMany(DpiaPersonalData::class)->orderBy('order_column');
    }

    /**
     * @return HasMany<DpiaRisk, $this>
     */
    public function risks(): HasMany
    {
        return $this->hasMany(DpiaRisk::class)->orderBy('order_column');
    }

    /**
     * @return HasMany<DpiaMeasure, $this>
     */
    public function measures(): HasMany
    {
        return $this->hasMany(DpiaMeasure::class)->orderBy('order_column');
    }

    /**
     * Whether the AP has to be consulted before the processing may start.
     *
     * Artikel 36 AVG requires prior consultation when the DPIA shows a high
     * risk that the controller cannot bring down to an acceptable level. That
     * is what a remaining high residual risk after all measures means, so the
     * answer is derived rather than left to the invuller to remember.
     */
    public function requiresApConsultation(): bool
    {
        return $this->measures->withHighResidualRisk()->isNotEmpty();
    }

    /**
     * The highest residual risk level left after the measures, or null when no
     * measure has been scored yet.
     */
    public function highestResidualRiskLevel(): ?RiskLevel
    {
        $highest = null;

        foreach ($this->measures as $measure) {
            $level = $measure->residual_level;

            if (!$level instanceof RiskLevel) {
                continue;
            }

            if ($level === RiskLevel::HIGH) {
                return RiskLevel::HIGH;
            }

            if ($highest === RiskLevel::MEDIUM) {
                continue;
            }

            $highest = $level;
        }

        return $highest;
    }
}
