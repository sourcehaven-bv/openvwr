<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\Avg\AvgResponsibleProcessingRecordCollection;
use App\Collections\TagCollection;
use App\Enums\LabelColor;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\TenantAware;
use App\Models\Wpg\WpgProcessingRecord;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property string $name
 * @property ?LabelColor $color
 *
 * @property-read AvgResponsibleProcessingRecordCollection $avgResponsibleProcessingRecords
 */
class Tag extends Model implements TenantAware
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;
    use HasOrganisation;
    use HasSoftDeletes;
    use HasTimestamps;
    use HasUuidAsId;

    protected static string $collectionClass = TagCollection::class;
    protected $fillable = [
        'name',
        'color',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'color' => LabelColor::class,
        ];
    }

    /**
     * @return MorphToMany<AvgResponsibleProcessingRecord, $this>
     */
    public function avgResponsibleProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgResponsibleProcessingRecord::class, 'taggable');
    }

    /**
     * @return MorphToMany<AvgProcessorProcessingRecord, $this>
     */
    public function avgProcessorProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgProcessorProcessingRecord::class, 'taggable');
    }

    /**
     * @return MorphToMany<WpgProcessingRecord, $this>
     */
    public function wpgProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(WpgProcessingRecord::class, 'taggable');
    }
}
