<?php

declare(strict_types=1);

namespace Database\Factories\Avg;

use App\Enums\CoreEntityDataCollectionSource;
use App\Enums\EntityNumberType;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgProcessorProcessingRecordService;
use App\Models\ContactPerson;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\EntityNumber;
use App\Models\FgRemark;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Remark;
use App\Models\Responsible;
use App\Models\Snapshot;
use App\Models\Stakeholder;
use App\Models\System;
use Illuminate\Database\Eloquent\Factories\Factory;

use function __;

/**
 * @extends Factory<AvgProcessorProcessingRecord>
 */
class AvgProcessorProcessingRecordFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'import_id' => $this->faker->optional()->importId(),

            'data_collection_source' => $this->faker->randomElement(CoreEntityDataCollectionSource::cases()),
            'name' => $this->faker->name(),

            'has_processors' => $this->faker->boolean(),
            'has_security' => $this->faker->boolean(),
            'responsibility_distribution' => $this->faker->text(),

            'has_pseudonymization' => $this->faker->boolean(),
            'pseudonymization' => $this->faker->text(),

            'outside_eu' => $this->faker->boolean(),
            'country' => $this->faker->optional()->randomElement(__('general.country_options')),
            'outside_eu_protection_level' => $this->faker->boolean(),
            'outside_eu_protection_level_description' => $this->faker->text(),
            'decision_making' => $this->faker->boolean(),
            'logic' => $this->faker->text(),
            'importance_consequences' => $this->faker->text(),
            'geb_pia' => $this->faker->boolean(),

            'has_goal' => $this->faker->boolean(),
            'has_systems' => $this->faker->boolean(),

            'has_involved' => $this->faker->boolean(),
            'suspects' => $this->faker->boolean(),
            'victims' => $this->faker->boolean(),
            'convicts' => $this->faker->boolean(),

            'review_at' => $this->faker->optional()->calendarDate(),
            'public_from' => $this->faker->optional()->anyDate(),

            'avg_processor_processing_record_service_id' => AvgProcessorProcessingRecordService::factory(state: [
                'enabled' => true,
            ]),
            'entity_number_id' => EntityNumber::factory(state: ['type' => EntityNumberType::REGISTER]),
        ];
    }

    /**
     * Valid state = a model without form-errors when submitted (no missing required fields or invalid values)
     */
    public function withValidState(): self
    {
        return $this
            ->withResponsibles();
    }

    public function withAllRelatedEntities(): self
    {
        return self::withAvgGoals()
            ->withContactPersons()
            ->withDataBreachRecords()
            ->withDocuments()
            ->withFgRemark()
            ->withProcessors()
            ->withReceivers()
            ->withRemarks()
            ->withResponsibles()
            ->withSnapshots()
            ->withStakeholders()
            ->withSystems();
    }

    public function withAvgGoals(?int $count = null): self
    {
        return $this->afterCreating(function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord) use ($count): void {
            AvgGoal::factory()
                ->hasAttached($avgProcessorProcessingRecord)
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withContactPersons(?int $count = null): self
    {
        return $this->afterCreating(function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord) use ($count): void {
            ContactPerson::factory()
                ->hasAttached($avgProcessorProcessingRecord)
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->withAddress()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withDataBreachRecords(?int $count = null): self
    {
        return $this->afterCreating(function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord) use ($count): void {
            DataBreachRecord::factory()
                ->hasAttached($avgProcessorProcessingRecord)
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withDocuments(?int $count = null): self
    {
        return $this->afterCreating(function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord) use ($count): void {
            Document::factory()
                ->hasAttached($avgProcessorProcessingRecord)
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->count($count ?? $this->faker->numberBetween(0, 2))
                ->create();
        });
    }

    public function withFgRemark(): self
    {
        return $this->afterCreating(static function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord): void {
            FgRemark::factory()
                ->for($avgProcessorProcessingRecord)
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->create();
        });
    }

    public function withProcessors(?int $count = null): self
    {
        return $this->afterCreating(function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord) use ($count): void {
            Processor::factory()
                ->hasAttached($avgProcessorProcessingRecord)
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->withAddress()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withRemarks(?int $count = null): self
    {
        return $this->afterCreating(function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord) use ($count): void {
            Remark::factory()
                ->for($avgProcessorProcessingRecord, 'remarkRelatable')
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->recycle($avgProcessorProcessingRecord->organisation->users)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withReceivers(?int $count = null): self
    {
        return $this->afterCreating(function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord) use ($count): void {
            Receiver::factory()
                ->hasAttached($avgProcessorProcessingRecord)
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withResponsibles(?int $count = null): self
    {
        return $this->afterCreating(function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord) use ($count): void {
            Responsible::factory()
                ->hasAttached($avgProcessorProcessingRecord)
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->withAddress()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withSnapshots(?int $count = null): self
    {
        return $this->afterCreating(function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord) use ($count): void {
            Snapshot::factory()
                ->for($avgProcessorProcessingRecord, 'snapshotSource')
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->withSnapshotTransitions()
                ->withSnapshotData()
                ->count($count ?? $this->faker->numberBetween(0, 3))
                ->create();
        });
    }

    public function withStakeholders(?int $count = null): self
    {
        return $this->afterCreating(function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord) use ($count): void {
            Stakeholder::factory()
                ->hasAttached($avgProcessorProcessingRecord)
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withSystems(?int $count = null): self
    {
        return $this->afterCreating(function (AvgProcessorProcessingRecord $avgProcessorProcessingRecord) use ($count): void {
            System::factory()
                ->hasAttached($avgProcessorProcessingRecord)
                ->recycle($avgProcessorProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }
}
