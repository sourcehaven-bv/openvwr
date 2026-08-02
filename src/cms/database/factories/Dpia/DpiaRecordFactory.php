<?php

declare(strict_types=1);

namespace Database\Factories\Dpia;

use App\Enums\Dpia\DpiaSubjectType;
use App\Enums\EntityNumberType;
use App\Models\Dpia\DpiaRecord;
use App\Models\EntityNumber;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DpiaRecord>
 */
class DpiaRecordFactory extends Factory
{
    protected $model = DpiaRecord::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'entity_number_id' => EntityNumber::factory(state: ['type' => EntityNumberType::REGISTER]),

            'name' => $this->faker->sentence(3),
            'subject_type' => $this->faker->randomElement(DpiaSubjectType::cases()),

            'proposal_description' => $this->faker->paragraph(),
            'proposal_motivation' => $this->faker->paragraph(),
            'personal_data_description' => $this->faker->paragraph(),
            'personal_data_sources' => $this->faker->sentence(),
            'processing_description' => $this->faker->paragraph(),
            'techniques_description' => $this->faker->paragraph(),
            'automated_decision_making' => $this->faker->boolean(),
            'profiling' => $this->faker->boolean(),
            'cloud_processing' => $this->faker->boolean(),
            'big_data_processing' => $this->faker->boolean(),
            'purpose_description' => $this->faker->paragraph(),
            'parties_description' => $this->faker->paragraph(),
            'interests_description' => $this->faker->paragraph(),
            'processing_locations' => $this->faker->country(),
            'outside_eea' => false,
            'legal_policy_framework' => $this->faker->sentence(),
            'retention_periods' => $this->faker->sentence(),
            'retention_motivation' => $this->faker->paragraph(),

            'legal_basis' => $this->faker->sentence(),
            'legal_basis_conditions' => $this->faker->paragraph(),
            'special_categories' => false,
            'national_identification_number' => false,
            'further_processing' => false,
            'necessity_proportionality' => $this->faker->paragraph(),
            'necessity_subsidiarity' => $this->faker->paragraph(),
            'data_subject_rights_procedure' => $this->faker->paragraph(),
            'rights_restricted' => false,

            'data_subjects_consulted' => $this->faker->boolean(),
            'ap_consultation_required' => false,

            'assessed_at' => $this->faker->optional()->date(),
            'review_at' => $this->faker->optional()->date(),
        ];
    }
}
