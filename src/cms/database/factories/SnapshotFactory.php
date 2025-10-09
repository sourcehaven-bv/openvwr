<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Snapshot;
use App\Models\SnapshotData;
use App\Models\SnapshotTransition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Snapshot>
 */
class SnapshotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var AvgResponsibleProcessingRecord|AlgorithmRecord|Processor|Receiver $snapshotSource */
        $snapshotSource = $this->faker->randomElement([
            AvgResponsibleProcessingRecord::class,
            AlgorithmRecord::class,
            Processor::class,
            Receiver::class,
        ]);

        return [
            'organisation_id' => Organisation::factory(),
            'snapshot_source_id' => $snapshotSource::factory(),
            'snapshot_source_type' => $snapshotSource,

            'name' => $this->faker->word(),
            'version' => $this->faker->unique()->randomNumber(),
            'state' => $this->faker->snapshotState(),
            'replaced_at' => $this->faker->optional()->dateTimeBetween('-1 month'),
        ];
    }

    public function withSnapshotTransitions(?int $count = null): self
    {
        return $this->afterCreating(function (Snapshot $snapshot) use ($count): void {
            SnapshotTransition::factory()
                ->for($snapshot)
                ->recycle($snapshot->organisation)
                ->recycle($snapshot->organisation->users)
                ->count($count ?? $this->faker->numberBetween(0, 3))
                ->create();
        });
    }

    /**
     * @param array<string, mixed> $snapshotDataAttributes
     */
    public function withSnapshotData(array $snapshotDataAttributes = []): static
    {
        return $this->afterCreating(static function (Snapshot $snapshot) use ($snapshotDataAttributes): void {
            $factory = SnapshotData::factory()->for($snapshot);

            if (isset($snapshotDataAttributes['forPublicWebsite']) && $snapshotDataAttributes['forPublicWebsite'] === true) {
                unset($snapshotDataAttributes['forPublicWebsite']);
                $factory = $factory->forPublicWebsite();
            }

            $factory->create($snapshotDataAttributes);
        });
    }
}
