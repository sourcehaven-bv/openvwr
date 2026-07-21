<?php

declare(strict_types=1);

namespace Database\Factories\Wpg;

use App\Enums\CoreEntityDataCollectionSource;
use App\Enums\EntityNumberType;
use App\Models\ContactPerson;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\EntityNumber;
use App\Models\FgRemark;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\Remark;
use App\Models\Responsible;
use App\Models\Snapshot;
use App\Models\System;
use App\Models\Wpg\WpgGoal;
use App\Models\Wpg\WpgProcessingRecord;
use App\Models\Wpg\WpgProcessingRecordService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WpgProcessingRecord>
 */
class WpgProcessingRecordFactory extends Factory
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
            'name' => $this->faker->word(),

            'suspects' => $this->faker->boolean(),
            'victims' => $this->faker->boolean(),
            'convicts' => $this->faker->boolean(),
            'police_justice' => $this->faker->boolean(),
            'third_parties' => $this->faker->boolean(),
            'third_party_explanation' => $this->faker->optional()->text(),

            'pseudonymization' => $this->faker->optional()->text(),

            'has_security' => $this->faker->boolean(),

            'decision_making' => $this->faker->boolean(),
            'logic' => $this->faker->text(),
            'consequences' => $this->faker->text(),

            'article_15' => $this->faker->boolean(),
            'article_15_a' => $this->faker->boolean(),
            'article_16' => $this->faker->boolean(),
            'article_17' => $this->faker->boolean(),
            'article_17_a' => $this->faker->boolean(),
            'article_18' => $this->faker->boolean(),
            'article_19' => $this->faker->boolean(),
            'article_20' => $this->faker->boolean(),
            'article_22' => $this->faker->boolean(),
            'article_23' => $this->faker->boolean(),
            'article_24' => $this->faker->boolean(),
            'has_processors' => $this->faker->boolean(),

            'police_race_or_ethnicity' => $this->faker->boolean(),
            'police_political_attitude' => $this->faker->boolean(),
            'police_faith_or_belief' => $this->faker->boolean(),
            'police_association_membership' => $this->faker->boolean(),
            'police_genetic' => $this->faker->boolean(),
            'police_identification' => $this->faker->boolean(),
            'police_health' => $this->faker->boolean(),
            'police_sexual_life' => $this->faker->boolean(),
            'explanation_available' => $this->faker->optional()->text(),
            'explanation_provisioning' => $this->faker->optional()->text(),
            'explanation_transfer' => $this->faker->optional()->text(),

            'geb_pia' => $this->faker->boolean(),

            'wpg_processing_record_service_id' => WpgProcessingRecordService::factory(state: [
                'enabled' => true,
            ]),
            'entity_number_id' => EntityNumber::factory(state: ['type' => EntityNumberType::REGISTER]),

            'review_at' => $this->faker->optional()->calendarDate(),
            'public_from' => $this->faker->optional()->anyDate(),
        ];
    }

    public function withValidState(): self
    {
        return $this
            ->state(function (): array {
                return [
                    'has_processors' => false,
                    'has_systems' => false,
                    'decision_making' => false,
                    'article_17_a' => false,
                    'third_parties' => false,
                    'has_security' => false,
                ];
            });
    }

    public function withAllRelatedEntities(): self
    {
        return self::withContactPersons()
            ->withProcessors()
            ->withResponsibles()
            ->withSnapshots()
            ->withSystems()
            ->withWpgGoals();
    }

    public function withContactPersons(?int $count = null): self
    {
        return $this->afterCreating(function (WpgProcessingRecord $wpgProcessingRecord) use ($count): void {
            ContactPerson::factory()
                ->hasAttached($wpgProcessingRecord)
                ->recycle($wpgProcessingRecord->organisation)
                ->withAddress()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withDataBreachRecords(?int $count = null): self
    {
        return $this->afterCreating(function (WpgProcessingRecord $wpgProcessingRecord) use ($count): void {
            DataBreachRecord::factory()
                ->hasAttached($wpgProcessingRecord)
                ->recycle($wpgProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withDocuments(?int $count = null): self
    {
        return $this->afterCreating(function (WpgProcessingRecord $wpgProcessingRecord) use ($count): void {
            Document::factory()
                ->hasAttached($wpgProcessingRecord)
                ->recycle($wpgProcessingRecord->organisation)
                ->count($count ?? $this->faker->numberBetween(0, 2))
                ->create();
        });
    }

    public function withFgRemark(): self
    {
        return $this->afterCreating(static function (WpgProcessingRecord $wpgProcessingRecord): void {
            FgRemark::factory()
                ->for($wpgProcessingRecord)
                ->recycle($wpgProcessingRecord->organisation)
                ->create();
        });
    }

    public function withProcessors(?int $count = null): self
    {
        return $this->afterCreating(function (WpgProcessingRecord $wpgProcessingRecord) use ($count): void {
            Processor::factory()
                ->hasAttached($wpgProcessingRecord)
                ->recycle($wpgProcessingRecord->organisation)
                ->withAddress()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withRemarks(?int $count = null): self
    {
        return $this->afterCreating(function (WpgProcessingRecord $wpgProcessingRecord) use ($count): void {
            Remark::factory()
                ->for($wpgProcessingRecord, 'remarkRelatable')
                ->recycle($wpgProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withResponsibles(?int $count = null): self
    {
        return $this->afterCreating(function (WpgProcessingRecord $wpgProcessingRecord) use ($count): void {
            Responsible::factory()
                ->hasAttached($wpgProcessingRecord)
                ->recycle($wpgProcessingRecord->organisation)
                ->withAddress()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withSnapshots(?int $count = null): self
    {
        return $this->afterCreating(function (WpgProcessingRecord $wpgProcessingRecord) use ($count): void {
            Snapshot::factory()
                ->for($wpgProcessingRecord, 'snapshotSource')
                ->recycle($wpgProcessingRecord->organisation)
                ->withSnapshotTransitions()
                ->withSnapshotData()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withSystems(?int $count = null): self
    {
        return $this->afterCreating(function (WpgProcessingRecord $wpgProcessingRecord) use ($count): void {
            System::factory()
                ->hasAttached($wpgProcessingRecord)
                ->recycle($wpgProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withWpgGoals(?int $count = null): self
    {
        return $this->afterCreating(function (WpgProcessingRecord $wpgProcessingRecord) use ($count): void {
            WpgGoal::factory()
                ->hasAttached($wpgProcessingRecord)
                ->recycle($wpgProcessingRecord->organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }
}
