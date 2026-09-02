<?php

declare(strict_types=1);

namespace App\Services\Snapshot;

use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use App\Models\States\SnapshotState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use Webmozart\Assert\Assert;

use function is_int;

readonly class SnapshotFactory
{
    public function __construct(
        private SnapshotStateTransitionService $snapshotStateTransitionService,
        private SnapshotDataFactory $snapshotDataFactory,
    ) {
    }

    /**
     * @param Model&SnapshotSource $snapshotSource
     * @param class-string<SnapshotState> $snapshotStateClass
     *
     * @throws Throwable
     */
    public function fromSnapshotSource(
        SnapshotSource $snapshotSource,
        string $snapshotStateClass = SnapshotState::DEFAULT_STATE,
        ?int $version = null,
    ): Snapshot {
        return DB::transaction(function () use ($snapshotSource, $version, $snapshotStateClass): Snapshot {
            if ($version === null) {
                $maxSnapshotVersion = $snapshotSource->snapshots()->max('version');
                $version = is_int($maxSnapshotVersion) ? $maxSnapshotVersion + 1 : 1;
            }

            $this->snapshotStateTransitionService->transitionSnapshotsToObsolete(
                $snapshotSource->getSnapshotsWithState($snapshotStateClass),
            );

            /** @var Snapshot $snapshot */
            $snapshot = $snapshotSource->snapshots()->create([
                'name' => Str::take($snapshotSource->getDisplayName(), 255),
                'organisation_id' => $snapshotSource->getOrganisation()->id,
                'version' => $version,
                'state' => $snapshotStateClass,
            ]);

            $this->fillSnapshot($snapshot, $snapshotSource);

            return $snapshot;
        });
    }

    /**
     * Rewrites an existing snapshot to match its source again, as if it had just been
     * created. Used for the concept snapshot, which follows the entity on every save
     * rather than freezing a moment: keeping the same row keeps its place in the
     * version list and its recorded transitions intact.
     *
     * @param Model&SnapshotSource $snapshotSource
     *
     * @throws Throwable
     */
    public function refreshSnapshot(Snapshot $snapshot, SnapshotSource $snapshotSource): Snapshot
    {
        return DB::transaction(function () use ($snapshot, $snapshotSource): Snapshot {
            $snapshot->name = Str::take($snapshotSource->getDisplayName(), 255);
            $snapshot->save();

            $snapshot->snapshotData()->delete();
            $snapshot->relatedSnapshotSources()->delete();
            $snapshot->unsetRelation('snapshotData');
            $snapshot->unsetRelation('relatedSnapshotSources');

            $this->fillSnapshot($snapshot, $snapshotSource);

            return $snapshot;
        });
    }

    /**
     * @param Model&SnapshotSource $snapshotSource
     */
    private function fillSnapshot(Snapshot $snapshot, SnapshotSource $snapshotSource): void
    {
        $this->snapshotDataFactory->createDataForSnapshot($snapshot);

        foreach ($snapshotSource->getRelatedSnapshotSources() as $relatedSnapshotSourceClass => $relatedSnapshotSourceCollection) {
            foreach ($relatedSnapshotSourceCollection as $relatedSnapshotSource) {
                Assert::isInstanceOf($relatedSnapshotSource, Model::class);

                $snapshot->relatedSnapshotSources()->create([
                    'snapshot_id' => $snapshot->id,
                    'snapshot_source_type' => $relatedSnapshotSourceClass,
                    'snapshot_source_id' => $relatedSnapshotSource->getKey(),
                ]);
            }
        }
    }
}
