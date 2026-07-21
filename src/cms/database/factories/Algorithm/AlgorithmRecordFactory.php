<?php

declare(strict_types=1);

namespace Database\Factories\Algorithm;

use App\Enums\EntityNumberType;
use App\Models\Algorithm\AlgorithmMetaSchema;
use App\Models\Algorithm\AlgorithmPublicationCategory;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Algorithm\AlgorithmStatus;
use App\Models\Algorithm\AlgorithmTheme;
use App\Models\Document;
use App\Models\EntityNumber;
use App\Models\FgRemark;
use App\Models\Organisation;
use App\Models\Snapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlgorithmRecord>
 */
class AlgorithmRecordFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'import_id' => $this->faker->optional()->importId(),

            'algorithm_theme_id' => AlgorithmTheme::factory(state: ['enabled' => true]),
            'algorithm_status_id' => AlgorithmStatus::factory(state: ['enabled' => true]),
            'algorithm_publication_category_id' => AlgorithmPublicationCategory::factory(state: ['enabled' => true]),
            'algorithm_meta_schema_id' => AlgorithmMetaSchema::factory(state: ['enabled' => true]),
            'entity_number_id' => EntityNumber::factory(state: ['type' => EntityNumberType::DATABREACH]),

            'name' => $this->faker->word(),
            'description' => $this->faker->optional()->sentence(),
            'start_date' => $this->faker->optional()->dateTime(),
            'end_date' => $this->faker->optional()->dateTime(),
            'contact_data' => $this->faker->optional()->sentence(),
            'source_link' => $this->faker->optional()->url(),
            'public_page_link' => $this->faker->optional()->url(),
            'public_from' => $this->faker->optional()->anyDate(),

            'resp_goal_and_impact' => $this->faker->optional()->sentence(),
            'resp_considerations' => $this->faker->optional()->sentence(),
            'resp_human_intervention' => $this->faker->optional()->sentence(),
            'resp_risk_analysis' => $this->faker->optional()->sentence(),
            'resp_legal_base_title' => $this->faker->optional()->sentence(),
            'resp_legal_base' => $this->faker->optional()->sentence(),
            'resp_legal_base_link' => $this->faker->optional()->url(),
            'resp_processor_registry_link' => $this->faker->optional()->url(),
            'resp_impact_tests' => $this->faker->optional()->sentence(),
            'resp_impact_test_links' => $this->faker->optional()->url(),
            'resp_impact_tests_description' => $this->faker->optional()->sentence(),

            'oper_data_title' => $this->faker->optional()->sentence(),
            'oper_data' => $this->faker->optional()->sentence(),
            'oper_links' => $this->faker->optional()->url(),
            'oper_technical_operation' => $this->faker->optional()->sentence(),
            'oper_supplier' => $this->faker->optional()->company(),
            'oper_source_code_link' => $this->faker->optional()->url(),

            'meta_lang' => $this->faker->optional()->countryISOAlpha3(),
            'meta_national_id' => $this->faker->optional()->slug(1),
            'meta_source_id' => $this->faker->optional()->slug(1),
            'meta_tags' => $this->faker->optional()->sentence(),
            'meta_date_of_development' => $this->faker->optional()->dateTime(),
            'meta_owner_algorithm' => $this->faker->optional()->name(),
            'meta_product_owner_algorithm' => $this->faker->optional()->name(),

            'impact_with_consequences' => $this->faker->optional()->boolean(),
            'impact_more_algorithms_applied' => $this->faker->optional()->boolean(),
            'impact_effect_on_outcome' => $this->faker->optional()->boolean(),
            'validation_answers_checked_by_product_owner' => $this->faker->optional()->boolean(),
        ];
    }

    public function withAllRelatedEntities(): self
    {
        return self::withDocuments()
            ->withFgRemark()
            ->withSnapshots();
    }

    public function withDocuments(?int $count = null): self
    {
        return $this->afterCreating(function (AlgorithmRecord $algorithmRecord) use ($count): void {
            Document::factory()
                ->hasAttached($algorithmRecord)
                ->recycle($algorithmRecord->organisation)
                ->count($count ?? $this->faker->numberBetween(0, 2))
                ->create();
        });
    }

    public function withFgRemark(): self
    {
        return $this->afterCreating(static function (AlgorithmRecord $algorithmRecord): void {
            FgRemark::factory()
                ->for($algorithmRecord)
                ->recycle($algorithmRecord->organisation)
                ->create();
        });
    }

    public function withSnapshots(?int $count = null): self
    {
        return $this->afterCreating(function (AlgorithmRecord $algorithmRecord) use ($count): void {
            Snapshot::factory()
                ->for($algorithmRecord, 'snapshotSource')
                ->recycle($algorithmRecord->organisation)
                ->withSnapshotTransitions()
                ->withSnapshotData()
                ->count($count ?? $this->faker->numberBetween(0, 3))
                ->create();
        });
    }
}
