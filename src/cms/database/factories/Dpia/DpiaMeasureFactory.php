<?php

declare(strict_types=1);

namespace Database\Factories\Dpia;

use App\Enums\Dpia\MeasureType;
use App\Enums\Dpia\RiskLevel;
use App\Models\Dpia\DpiaMeasure;
use App\Models\Dpia\DpiaRecord;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DpiaMeasure>
 */
class DpiaMeasureFactory extends Factory
{
    protected $model = DpiaMeasure::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'dpia_record_id' => DpiaRecord::factory(),

            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(MeasureType::cases()),
            'origin' => $this->faker->sentence(),
            // Default to a residual risk that does not force AP consultation;
            // tests that need that path set it explicitly.
            'residual_level' => $this->faker->randomElement([RiskLevel::LOW, RiskLevel::MEDIUM]),
            'owner' => $this->faker->name(),
            'order_column' => 0,
        ];
    }
}
