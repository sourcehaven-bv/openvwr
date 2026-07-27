<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\RelatedSnapshotSourceCollection;
use App\Components\Uuid\UuidInterface;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\SnapshotSource;
use Database\Factories\RelatedSnapshotSourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property UuidInterface $snapshot_id
 * @property class-string<SnapshotSource&Model> $snapshot_source_type
 * @property UuidInterface $snapshot_source_id
 *
 * The polymorphic target carries no foreign key (uuidMorphs indexes only), so
 * a hard-deleted source leaves an orphan row whose relation resolves to null.
 *
 * @property-read (SnapshotSource&Model)|null $snapshotSource
 * @property-read Snapshot $snapshot
 */
class RelatedSnapshotSource extends Model
{
    /** @use HasFactory<RelatedSnapshotSourceFactory> */
    use HasFactory;
    use HasTimestamps;
    use HasUuidAsId;

    protected static string $collectionClass = RelatedSnapshotSourceCollection::class;
    protected $fillable = [
        'snapshot_id',
        'snapshot_source_id',
        'snapshot_source_type',
    ];

    public function casts(): array
    {
        return [
            'snapshot_id' => UuidCast::class,
            'snapshot_source_id' => UuidCast::class,
        ];
    }

    /**
     * The owner key must be passed explicitly: without it MorphTo falls back to
     * $result->getKey(), which HasUuidAsId overrides to return a UuidInterface
     * object, and that cannot be used as an array offset when matching results.
     * Naming the relation 'snapshotSource' keeps eager-loaded results readable
     * through the accessor of the same name.
     *
     * @return MorphTo<Model, $this>
     */
    public function snapshotSource(): MorphTo
    {
        return $this->morphTo('snapshotSource', 'snapshot_source_type', 'snapshot_source_id', 'id')
            ->withTrashed();
    }

    /**
     * @return BelongsTo<Snapshot, $this>
     */
    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(Snapshot::class);
    }
}
