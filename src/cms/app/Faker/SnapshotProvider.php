<?php

declare(strict_types=1);

namespace App\Faker;

use App\Components\Uuid\UuidInterface;
use App\Models\States\Snapshot\Obsolete;
use App\Models\States\SnapshotState;
use Closure;
use Faker\Provider\Miscellaneous;
use Webmozart\Assert\Assert;

use function in_array;
use function sprintf;

class SnapshotProvider extends Miscellaneous
{
    /** @var array<string, array<class-string<SnapshotState>>> */
    private array $groups = [];

    /**
     * @param array<class-string<SnapshotState>> $excluded
     */
    public function snapshotState(array $excluded = []): Closure
    {
        return function (array $attributes) use ($excluded): string {
            $snapshotSourceId = $attributes['snapshot_source_id'];
            $snapshotSourceType = $attributes['snapshot_source_type'];

            Assert::string($snapshotSourceType);
            Assert::isInstanceOf($snapshotSourceId, UuidInterface::class);

            $group = sprintf('%s-%s', $snapshotSourceType, $snapshotSourceId->toString());
            $used = $this->groups[$group] ?? [];

            /** @var class-string<SnapshotState>|null $state */
            $state = SnapshotState::all()
                ->reject(static function (string $state) use ($excluded, $used) {
                    if ($state === Obsolete::class) {
                        return true;
                    }

                    return in_array($state, $excluded, true) || in_array($state, $used, true);
                })
                ->first();

            if ($state === null) {
                return SnapshotState::OBSOLETE_STATE;
            }

            $this->groups[$group][] = $state;

            return $state;
        };
    }
}
