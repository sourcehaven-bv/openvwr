<?php

declare(strict_types=1);

namespace Database\Factories\Avg;

use App\Enums\CoreEntityDataCollectionSource;
use App\Enums\EntityNumberType;
use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
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
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AvgResponsibleProcessingRecord>
 */
class AvgResponsibleProcessingRecordFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $decision_making = $this->faker->boolean();

        return [
            'organisation_id' => Organisation::factory(),
            'import_id' => $this->faker->optional()->importId(),

            'data_collection_source' => $this->faker->randomElement(CoreEntityDataCollectionSource::cases()),
            'name' => $this->faker->word(),
            'responsibility_distribution' => $this->faker->optional()->sentence(),
            'has_pseudonymization' => $this->faker->boolean(),
            'pseudonymization' => $this->faker->optional()->sentence(),
            'outside_eu' => $this->faker->boolean(),
            'outside_eu_description' => $this->faker->optional()->sentence(),
            'outside_eu_protection_level' => $this->faker->boolean(),
            'outside_eu_protection_level_description' => $this->faker->optional()->sentence(),
            'decision_making' => $decision_making,
            'logic' => $decision_making ? $this->faker->sentence() : null,
            'importance_consequences' => $decision_making ? $this->faker->sentence() : null,
            'geb_dpia_executed' => $this->faker->boolean(),
            'geb_dpia_automated' => $this->faker->boolean(),
            'geb_dpia_large_scale_processing' => $this->faker->boolean(),
            'geb_dpia_large_scale_monitoring' => $this->faker->boolean(),
            'geb_dpia_list_required' => $this->faker->boolean(),
            'geb_dpia_criteria_wp248' => $this->faker->boolean(),
            'geb_dpia_high_risk_freedoms' => $this->faker->boolean(),

            'avg_responsible_processing_record_service_id' => AvgResponsibleProcessingRecordService::factory(state: ['enabled' => true]),
            'entity_number_id' => EntityNumber::factory(state: ['type' => EntityNumberType::REGISTER]),

            'has_processors' => $this->faker->boolean(),
            'has_security' => $this->faker->boolean(),
            'has_systems' => $this->faker->boolean(),
            'review_at' => $this->faker->optional()->calendarDate(),
            'public_from' => $this->faker->optional()->anyDate(),
        ];
    }

    /**
     * Valid state = a model without form-errors when submitted (no missing required fields or invalid values)
     */
    public function withValidState(): self
    {
        return $this
            ->withResponsibles()
            ->state(function (): array {
                return [
                    'has_processors' => false,
                    'decision_making' => false,
                    'has_systems' => false,
                    'has_security' => false,
                    'outside_eu' => false,
                    'geb_dpia_executed' => true,
                ];
            });
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
            ->withSystems()
            ->withTags();
    }

    public function withAvgGoals(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            AvgGoal::factory()
                ->hasAttached($avgResponsibleProcessingRecord)
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withContactPersons(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            ContactPerson::factory()
                ->hasAttached($avgResponsibleProcessingRecord)
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->withAddress()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withDataBreachRecords(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            DataBreachRecord::factory()
                ->hasAttached($avgResponsibleProcessingRecord)
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withDocuments(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            Document::factory()
                ->hasAttached($avgResponsibleProcessingRecord)
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->count($count ?? $this->faker->numberBetween(0, 2))
                ->create();
        });
    }

    public function withFgRemark(): self
    {
        return $this->afterCreating(static function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord): void {
            FgRemark::factory()
                ->for($avgResponsibleProcessingRecord)
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->create();
        });
    }

    public function withProcessors(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            Processor::factory()
                ->hasAttached($avgResponsibleProcessingRecord)
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->withAddress()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withReceivers(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            Receiver::factory()
                ->hasAttached($avgResponsibleProcessingRecord)
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withRemarks(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            Remark::factory()
                ->for($avgResponsibleProcessingRecord, 'remarkRelatable')
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->recycle($avgResponsibleProcessingRecord->organisation->users)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withResponsibles(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            Responsible::factory()
                ->hasAttached($avgResponsibleProcessingRecord)
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->withAddress()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withSnapshots(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            Snapshot::factory()
                ->for($avgResponsibleProcessingRecord, 'snapshotSource')
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->withSnapshotTransitions()
                ->withSnapshotData()
                ->count($count ?? $this->faker->numberBetween(0, 3))
                ->create();
        });
    }

    public function withPublishedSnapshot(): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord): void {
            Snapshot::factory()
                ->for($avgResponsibleProcessingRecord, 'snapshotSource')
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->withSnapshotTransitions()
                ->withSnapshotData(['forPublicWebsite' => true])
                ->create([
                    'state' => 'established',
                ]);
        });
    }

    public function withStakeholders(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            Stakeholder::factory()
                ->withStakeholderDataItems()
                ->hasAttached($avgResponsibleProcessingRecord)
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withSystems(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            System::factory()
                ->hasAttached($avgResponsibleProcessingRecord)
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withTags(?int $count = null): self
    {
        return $this->afterCreating(function (AvgResponsibleProcessingRecord $avgResponsibleProcessingRecord) use ($count): void {
            Tag::factory()
                ->hasAttached($avgResponsibleProcessingRecord)
                ->recycle($avgResponsibleProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }
}
