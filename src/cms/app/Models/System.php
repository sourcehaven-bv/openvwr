<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\Avg\AvgProcessorProcessingRecordCollection;
use App\Collections\Avg\AvgResponsibleProcessingRecordCollection;
use App\Collections\SystemCollection;
use App\Collections\Wpg\WpgProcessingRecordCollection;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasSnapshots;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\SnapshotSource;
use App\Models\Contracts\TenantAware;
use App\Models\Wpg\WpgProcessingRecord;
use Database\Factories\SystemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property string|null $import_id
 * @property string|null $description
 *
 * @property-read AvgProcessorProcessingRecordCollection $avgProcessorProcessingRecords
 * @property-read AvgResponsibleProcessingRecordCollection $avgResponsibleProcessingRecords
 * @property-read WpgProcessingRecordCollection $wpgProcessingRecords
 */
class System extends Model implements SnapshotSource, TenantAware
{
    /** @use HasFactory<SystemFactory> */
    use HasFactory;
    use HasOrganisation;
    use HasSnapshots;
    use HasSoftDeletes;
    use HasTags;
    use HasTimestamps;
    use HasUuidAsId;

    protected static string $collectionClass = SystemCollection::class;
    protected $fillable = [
        'description',
        'import_id',
    ];

    /**
     * @return MorphToMany<AvgProcessorProcessingRecord, $this>
     */
    public function avgProcessorProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgProcessorProcessingRecord::class, 'system_relatable');
    }

    /**
     * @return MorphToMany<AvgResponsibleProcessingRecord, $this>
     */
    public function avgResponsibleProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgResponsibleProcessingRecord::class, 'system_relatable');
    }

    /**
     * @return MorphToMany<WpgProcessingRecord, $this>
     */
    public function wpgProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(WpgProcessingRecord::class, 'system_relatable');
    }

    public function getDisplayName(): string
    {
        return $this->getDisplayNameOrDefaultValue($this->description);
    }
}
