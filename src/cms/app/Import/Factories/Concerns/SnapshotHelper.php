<?php

declare(strict_types=1);

namespace App\Import\Factories\Concerns;

use App\Config\Config;
use App\Models\Contracts\Reviewable;
use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\SnapshotState;
use App\Services\Snapshot\SnapshotFactory;
use App\ValueObjects\CalendarDate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\Exceptions\InvalidConfig;
use Throwable;
use Webmozart\Assert\Assert;

use function array_key_exists;

trait SnapshotHelper
{
    /**
     * @throws InvalidConfig
     * @throws Throwable
     */
    final protected function createSnapshot(
        Model&SnapshotSource $snapshotSource,
        int $version,
        string $status,
        SnapshotFactory $snapshotFactory,
    ): void {
        $snapshotStateConversions = Config::array('import.value_converters.snapshot_state');
        if (!array_key_exists($status, $snapshotStateConversions)) {
            return;
        }
        $snapshotState = $snapshotStateConversions[$status];
        Assert::classExists($snapshotState);
        Assert::subclassOf($snapshotState, SnapshotState::class);

        $snapshot = $snapshotFactory->fromSnapshotSource($snapshotSource, $snapshotState, $version);

        if ($snapshotState === Established::class && $snapshotSource instanceof Reviewable) {
            $this->setReviewAt($snapshotSource);
        }

        $this->createRelatedSnapshots($snapshot, $snapshotState, $snapshotFactory);
    }

    private function setReviewAt(Model&Reviewable&SnapshotSource $snapshotSource): void
    {
        $updatedAt = $snapshotSource->getAttribute('updated_at');
        Assert::isInstanceOf($updatedAt, CarbonImmutable::class);



        $reviewAt = $updatedAt
            ->setTimezone(Config::string('app.display_timezone'))
            ->floorDay()
            ->addMonths($snapshotSource->getOrganisation()->review_at_default_in_months)
            ->format('Y-m-d');

        $snapshotSource->setReviewAt(CalendarDate::createFromFormat('Y-m-d', $reviewAt));
        $snapshotSource->save();
    }

    /**
     * @param class-string<SnapshotState> $snapshotState
     *
     * @throws Throwable
     */
    private function createRelatedSnapshots(Snapshot $snapshot, string $snapshotState, SnapshotFactory $snapshotFactory): void
    {
        foreach ($snapshot->relatedSnapshotSources as $relatedSnapshotSource) {
            // The polymorphic target carries no foreign key, so a hard-deleted
            // source leaves an orphan row that resolves to null.
            $snapshotSource = $relatedSnapshotSource->snapshotSource;
            if ($snapshotSource === null) {
                continue;
            }

            if ($snapshotSource->getLatestSnapshotWithState([Established::class]) !== null) {
                continue;
            }

            if ($snapshotState === Established::class) {
                $snapshotFactory->fromSnapshotSource($snapshotSource, $snapshotState);
                continue;
            }

            if (
                $snapshotState === Approved::class
                && $snapshotSource->getLatestSnapshotWithState([Approved::class]) === null
            ) {
                $snapshotFactory->fromSnapshotSource($snapshotSource, $snapshotState);
                continue;
            }

            if (
                $snapshotState === InReview::class
                && $snapshotSource->getLatestSnapshotWithState([InReview::class]) === null
            ) {
                $snapshotFactory->fromSnapshotSource($snapshotSource, $snapshotState);
            }
        }
    }
}
