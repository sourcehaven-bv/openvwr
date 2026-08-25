<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EntityNumberType;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\EntityNumber;
use App\Models\FgRemark;
use App\Models\Organisation;
use App\Models\Responsible;
use App\Models\States\DataBreachRecord\Reported;
use App\Models\States\DataBreachRecordState;
use App\Models\Wpg\WpgProcessingRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

use function __;

/**
 * @extends Factory<DataBreachRecord>
 */
class DataBreachRecordFactory extends Factory
{
    protected $model = DataBreachRecord::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'entity_number_id' => EntityNumber::factory(state: ['type' => EntityNumberType::REGISTER]),

            'name' => $this->faker->word(),
            'type' => $this->faker->randomElement(__('data_breach_record.type_options')),
            'state' => Reported::class,
            'reported_at' => $this->faker->optional()->date(),
            'ap_reported' => $this->faker->boolean(),

            'discovered_at' => $this->faker->optional()->date(),
            'started_at' => $this->faker->optional()->date(),
            'ended_at' => $this->faker->optional()->date(),
            'ap_reported_at' => $this->faker->optional()->date(),
            'completed_at' => $this->faker->optional()->date(),

            'reported_to_involved' => $this->faker->boolean(),
            'involved_people' => $this->faker->sentence(),
            'summary' => $this->faker->sentence(),
            'estimated_risk' => $this->faker->sentence(),
            'measures' => $this->faker->sentence(),

            'fg_reported' => $this->faker->boolean(),

            'cross_border' => $this->faker->boolean(),
            'affected_count_known' => $this->faker->boolean(),
        ];
    }

    /**
     * @param class-string<DataBreachRecordState> $state
     */
    public function inState(string $state): self
    {
        return $this->state(function () use ($state): array {
            return ['state' => $state];
        });
    }

    /**
     * Valid state = a model without form-errors when submitted (no missing required fields or invalid values)
     */
    public function withValidState(): self
    {
        return $this
            ->state(function (): array {
                return [
                    'discovered_at' => $this->faker->dateTime(),
                ];
            });
    }

    public function withAllRelatedEntities(): self
    {
        return self::withAvgProcessorProcessingRecords()
            ->withAvgResponsibleProcessingRecords()
            ->withDocuments()
            ->withFgRemark()
            ->withResponsibles()
            ->withWpgProcessingRecords();
    }

    public function withAvgProcessorProcessingRecords(?int $count = null): self
    {
        return $this->afterCreating(function (DataBreachRecord $dataBreachRecord) use ($count): void {
            AvgProcessorProcessingRecord::factory()
                ->hasAttached($dataBreachRecord)
                ->recycle($dataBreachRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withAvgResponsibleProcessingRecords(?int $count = null): self
    {
        return $this->afterCreating(function (DataBreachRecord $dataBreachRecord) use ($count): void {
            AvgResponsibleProcessingRecord::factory()
                ->hasAttached($dataBreachRecord)
                ->recycle($dataBreachRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withDocuments(?int $count = null): self
    {
        return $this->afterCreating(function (DataBreachRecord $dataBreachRecord) use ($count): void {
            Document::factory()
                ->hasAttached($dataBreachRecord)
                ->recycle($dataBreachRecord->organisation)
                ->count($count ?? $this->faker->numberBetween(0, 2))
                ->create();
        });
    }

    public function withFgRemark(): self
    {
        return $this->afterCreating(static function (DataBreachRecord $dataBreachRecord): void {
            FgRemark::factory()
                ->for($dataBreachRecord)
                ->recycle($dataBreachRecord->organisation)
                ->create();
        });
    }

    public function withResponsibles(?int $count = null): self
    {
        return $this->afterCreating(function (DataBreachRecord $dataBreachRecord) use ($count): void {
            Responsible::factory()
                ->hasAttached($dataBreachRecord)
                ->recycle($dataBreachRecord->organisation)
                ->withAddress()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withWpgProcessingRecords(?int $count = null): self
    {
        return $this->afterCreating(function (DataBreachRecord $dataBreachRecord) use ($count): void {
            WpgProcessingRecord::factory()
                ->hasAttached($dataBreachRecord)
                ->recycle($dataBreachRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }
}
