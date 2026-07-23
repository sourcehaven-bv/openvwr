<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\RelatedSnapshotSourceCollection;
use App\Collections\SnapshotApprovalCollection;
use App\Collections\SnapshotApprovalLogCollection;
use App\Collections\SnapshotCollection;
use App\Components\Uuid\UuidInterface;
use App\Models\Builders\SnapshotBuilder;
use App\Models\Casts\UuidCast;
use App\Models\Concerns\HasOrganisation;
use App\Models\Concerns\HasTimestamps;
use App\Models\Concerns\HasUuidAsId;
use App\Models\Contracts\SnapshotSource;
use App\Models\Contracts\TenantAware;
use App\Models\Scopes\OrderByCreatedAtAscScope;
use App\Models\States\SnapshotState;
use Carbon\CarbonImmutable;
use Database\Factories\SnapshotFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\ModelStates\HasStates;
use Spatie\ModelStates\HasStatesContract;

/**
 * @property string $name
 * @property int $version
 * @property SnapshotState $state
 * @property CarbonImmutable|null $replaced_at
 * @property CarbonImmutable|null $established_at
 * @property string $snapshot_source_type
 * @property UuidInterface $snapshot_source_id
 *
 * @property-read SnapshotApprovalCollection $snapshotApprovals
 * @property-read SnapshotApprovalLogCollection $snapshotApprovalLogs
 * @property-read SnapshotData|null $snapshotData
 * @property-read (SnapshotSource&Model)|null $snapshotSource
 * @property-read RelatedSnapshotSourceCollection $relatedSnapshotSources
 * @property-read Collection<int, SnapshotTransition> $snapshotTransitions
 */
#[ScopedBy([OrderByCreatedAtAscScope::class])]
#[UseEloquentBuilder(SnapshotBuilder::class)]
class Snapshot extends Model implements HasStatesContract, TenantAware
{
    /** @use HasFactory<SnapshotFactory> */
    use HasFactory;
    use HasOrganisation;
    use HasStates;
    use HasTimestamps;
    use HasUuidAsId;

    protected static string $collectionClass = SnapshotCollection::class;
    protected $fillable = [
        'name',
        'version',
        'state',
    ];

    public function casts(): array
    {
        return [
            'snapshot_source_id' => UuidCast::class,
            'replaced_at' => 'datetime',
            'established_at' => 'datetime',
            'state' => SnapshotState::class,
        ];
    }

    /**
     * @return HasMany<SnapshotApproval, $this>
     */
    public function snapshotApprovals(): HasMany
    {
        return $this->hasMany(SnapshotApproval::class);
    }

    /**
     * @return HasMany<SnapshotApprovalLog, $this>
     */
    public function snapshotApprovalLogs(): HasMany
    {
        return $this->hasMany(SnapshotApprovalLog::class);
    }

    /**
     * @return HasOne<SnapshotData, $this>
     */
    public function snapshotData(): HasOne
    {
        return $this->hasOne(SnapshotData::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function snapshotSource(): MorphTo
    {
        return $this->morphTo(SnapshotSource::class, 'snapshot_source_type', 'snapshot_source_id', 'id');
    }

    /**
     * @return HasMany<RelatedSnapshotSource, $this>
     */
    public function relatedSnapshotSources(): HasMany
    {
        return $this->hasMany(RelatedSnapshotSource::class);
    }

    /**
     * @return HasMany<SnapshotTransition, $this>
     */
    public function snapshotTransitions(): HasMany
    {
        return $this->hasMany(SnapshotTransition::class)
            ->with('creator')
            ->oldest();
    }
}
