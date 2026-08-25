<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\Avg\AvgProcessorProcessingRecordCollection;
use App\Collections\Avg\AvgResponsibleProcessingRecordCollection;
use App\Collections\DataBreachRecordCollection;
use App\Collections\ResponsibleCollection;
use App\Collections\Wpg\WpgProcessingRecordCollection;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Concerns\HasAddress;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasSnapshots;
use App\Models\Concerns\HasSoftDeletes;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\SnapshotSource;
use App\Models\Contracts\TenantAware;
use App\Models\Wpg\WpgProcessingRecord;
use Database\Factories\ResponsibleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property string $name
 * @property string|null $import_id
 *
 * @property-read Address|null $address
 * @property-read AvgProcessorProcessingRecordCollection $avgProcessorProcessingRecords
 * @property-read AvgResponsibleProcessingRecordCollection $avgResponsibleProcessingRecords
 * @property-read WpgProcessingRecordCollection $wpgProcessingRecords
 * @property-read DataBreachRecordCollection $dataBreachRecords
 */
class Responsible extends Model implements SnapshotSource, TenantAware
{
    use HasAddress;
    /** @use HasFactory<ResponsibleFactory> */
    use HasFactory;
    use HasOrganisation;
    use HasSnapshots;
    use HasTags;
    use HasTimestamps;
    use HasSoftDeletes;
    use HasUuidAsId;

    protected static string $collectionClass = ResponsibleCollection::class;
    protected $fillable = [
        'name',
        'import_id',
    ];

    /**
     * @return MorphToMany<AvgProcessorProcessingRecord, $this>
     */
    public function avgProcessorProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgProcessorProcessingRecord::class, 'responsible_relatable');
    }

    /**
     * @return MorphToMany<AvgResponsibleProcessingRecord, $this>
     */
    public function avgResponsibleProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgResponsibleProcessingRecord::class, 'responsible_relatable');
    }

    /**
     * @return MorphToMany<DataBreachRecord, $this>
     */
    public function dataBreachRecords(): MorphToMany
    {
        return $this->morphedByMany(DataBreachRecord::class, 'responsible_relatable');
    }

    /**
     * @return MorphToMany<WpgProcessingRecord, $this>
     */
    public function wpgProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(WpgProcessingRecord::class, 'responsible_relatable');
    }
}
