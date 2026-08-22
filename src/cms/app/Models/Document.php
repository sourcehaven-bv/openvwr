<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\Algorithm\AlgorithmRecordCollection;
use App\Collections\Avg\AvgProcessorProcessingRecordCollection;
use App\Collections\Avg\AvgResponsibleProcessingRecordCollection;
use App\Collections\DataBreachRecordCollection;
use App\Collections\DocumentCollection;
use App\Collections\MediaCollection;
use App\Collections\Wpg\WpgProcessingRecordCollection;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Concerns\HasDefaultMediaCollections;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\TenantAware;
use App\Models\Wpg\WpgProcessingRecord;
use Carbon\CarbonImmutable;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property string $name
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $notify_at
 * @property string|null $location
 *
 * @property-read AlgorithmRecordCollection $algorithmRecords
 * @property-read AvgProcessorProcessingRecordCollection $avgProcessorProcessingRecords
 * @property-read AvgResponsibleProcessingRecordCollection $avgResponsibleProcessingRecords
 * @property-read DataBreachRecordCollection $dataBreachRecords
 * @property-read MediaCollection $media
 * @property-read WpgProcessingRecordCollection $wpgProcessingRecords
 */
class Document extends Model implements HasMedia, TenantAware
{
    use HasDefaultMediaCollections;
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;
    use HasOrganisation;
    use HasSoftDeletes;
    use HasTags;
    use HasTimestamps;
    use HasUuidAsId;

    protected static string $collectionClass = DocumentCollection::class;
    protected $fillable = [
        'name',
        'expires_at',
        'notify_at',
        'location',
    ];

    public function casts(): array
    {
        return [
            'expires_at' => 'date',
            'notify_at' => 'date',
        ];
    }

    /**
     * @return MorphToMany<AlgorithmRecord, $this>
     */
    public function algorithmRecords(): MorphToMany
    {
        return $this->morphedByMany(AlgorithmRecord::class, 'document_relatable');
    }

    /**
     * @return MorphToMany<AvgProcessorProcessingRecord, $this>
     */
    public function avgProcessorProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgProcessorProcessingRecord::class, 'document_relatable');
    }

    /**
     * @return MorphToMany<AvgResponsibleProcessingRecord, $this>
     */
    public function avgResponsibleProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgResponsibleProcessingRecord::class, 'document_relatable');
    }

    /**
     * @return MorphToMany<DataBreachRecord, $this>
     */
    public function dataBreachRecords(): MorphToMany
    {
        return $this->morphedByMany(DataBreachRecord::class, 'document_relatable');
    }

    /**
     * @return BelongsTo<DocumentType, $this>
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * @return MorphToMany<WpgProcessingRecord, $this>
     */
    public function wpgProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(WpgProcessingRecord::class, 'document_relatable');
    }
}
