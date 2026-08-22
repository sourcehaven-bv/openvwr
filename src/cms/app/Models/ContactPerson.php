<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\Avg\AvgProcessorProcessingRecordCollection;
use App\Collections\Avg\AvgResponsibleProcessingRecordCollection;
use App\Collections\ContactPersonCollection;
use App\Collections\Wpg\WpgProcessingRecordCollection;
use App\Components\Uuid\UuidInterface;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Casts\UuidCast;
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
use Database\Factories\ContactPersonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $import_id
 * @property ?UuidInterface $contact_person_position_id
 *
 * @property-read Address|null $address
 * @property-read AvgProcessorProcessingRecordCollection $avgProcessorProcessingRecords
 * @property-read AvgResponsibleProcessingRecordCollection $avgResponsibleProcessingRecords
 * @property-read ContactPersonPosition|null $contactPersonPosition
 * @property-read WpgProcessingRecordCollection $wpgProcessingRecords
 */
class ContactPerson extends Model implements SnapshotSource, TenantAware
{
    use HasAddress;
    /** @use HasFactory<ContactPersonFactory> */
    use HasFactory;
    use HasOrganisation;
    use HasSnapshots;
    use HasSoftDeletes;
    use HasTags;
    use HasTimestamps;
    use HasUuidAsId;

    protected static string $collectionClass = ContactPersonCollection::class;
    protected $fillable = [
        'contact_person_position_id',

        'name',
        'role',
        'email',
        'phone',
        'import_id',
    ];
    protected $table = 'contact_persons';

    public function casts(): array
    {
        return [
            'contact_person_position_id' => UuidCast::class,
        ];
    }

    /**
     * @return MorphToMany<AvgProcessorProcessingRecord, $this>
     */
    public function avgProcessorProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgProcessorProcessingRecord::class, 'contact_person_relatable');
    }

    /**
     * @return MorphToMany<AvgResponsibleProcessingRecord, $this>
     */
    public function avgResponsibleProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(AvgResponsibleProcessingRecord::class, 'contact_person_relatable');
    }

    /**
     * @return BelongsTo<ContactPersonPosition, $this>
     */
    public function contactPersonPosition(): BelongsTo
    {
        return $this->belongsTo(ContactPersonPosition::class, 'contact_person_position_id');
    }

    /**
     * @return MorphToMany<WpgProcessingRecord, $this>
     */
    public function wpgProcessingRecords(): MorphToMany
    {
        return $this->morphedByMany(WpgProcessingRecord::class, 'contact_person_relatable');
    }
}
