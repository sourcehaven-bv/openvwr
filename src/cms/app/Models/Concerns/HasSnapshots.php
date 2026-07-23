<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Attributes\RelatedSnapshotSource;
use App\Collections\SnapshotCollection;
use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use App\Models\States\SnapshotState;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use ReflectionClass;
use Webmozart\Assert\Assert;

use function call_user_func;

/**
 * @property-read SnapshotCollection $snapshots
 */
trait HasSnapshots
{
    final public function getDisplayName(): string
    {
        $name = $this->getAttribute('name');
        Assert::string($name, 'name-attribute should be a string');

        return $name;
    }

    final public function getDisplayNameOrDefaultValue(?string $name): string
    {
        if ($name === null) {
            $name = '—';
        }

        return $name;
    }

    /**
     * @param array<class-string<SnapshotState>> $snapshotStates
     */
    final public function getLatestSnapshotWithState(array $snapshotStates): ?Snapshot
    {
        $snapshotStates = new Collection($snapshotStates);
        $states = $snapshotStates->map(static function (string $snapshotState): string {
            return $snapshotState::$name;
        });

        return $this->snapshots()
            ->whereIn('state', $states)
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * @return MorphMany<Snapshot, $this>
     */
    final public function snapshots(): MorphMany
    {
        return $this->morphMany(Snapshot::class, 'snapshot_source');
    }

    final public function hasComparableSnapshots(): bool
    {
        return $this->snapshots()->count() >= 2;
    }

    final public function getLatestSnapshot(): ?Snapshot
    {
        return $this->snapshots()
            ->orderByDesc('version')
            ->first();
    }

    /**
     * @param class-string<SnapshotState> $state
     */
    final public function getSnapshotsWithState(string $state): SnapshotCollection
    {
        $snapshots = $this->snapshots()
            ->where('state', $state::$name)
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->get();
        Assert::isInstanceOf($snapshots, SnapshotCollection::class);

        return $snapshots;
    }

    /**
     * @return Collection<class-string<SnapshotSource>, Collection<int, SnapshotSource>>
     */
    final public function getRelatedSnapshotSources(): Collection
    {
        $relatedSnapshotSources = new Collection();

        $reflectionClass = new ReflectionClass($this);
        foreach ($reflectionClass->getMethods() as $method) {
            foreach ($method->getAttributes(RelatedSnapshotSource::class) as $attribute) {
                $callable = [$this, $method->name];
                Assert::isCallable($callable);

                $relation = call_user_func($callable);
                Assert::isInstanceOf($relation, Relation::class);

                $relatedSnapshotSources->put($attribute->getArguments()[0], $relation->get());
            }
        }

        return $relatedSnapshotSources;
    }
}
