<?php

declare(strict_types=1);

namespace Database\Factories\Dpia;

use App\Enums\Dpia\RiskLevel;
use App\Models\Dpia\DpiaRecord;
use App\Models\Dpia\DpiaRisk;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

use function count;

/**
 * @extends Factory<DpiaRisk>
 */
class DpiaRiskFactory extends Factory
{
    protected $model = DpiaRisk::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cases = RiskLevel::cases();
        $likelihood = $cases[$this->faker->numberBetween(0, count($cases) - 1)];
        $impact = $cases[$this->faker->numberBetween(0, count($cases) - 1)];

        return [
            'organisation_id' => Organisation::factory(),
            'dpia_record_id' => DpiaRecord::factory(),

            'title' => $this->faker->words(4, true),
            'description' => $this->faker->paragraph(),
            'origin' => $this->faker->sentence(),
            'likelihood' => $likelihood,
            'likelihood_motivation' => $this->faker->sentence(),
            'impact' => $impact,
            'impact_motivation' => $this->faker->sentence(),
            'level' => RiskLevel::suggest($likelihood, $impact),
            'level_motivation' => $this->faker->sentence(),
            'order_column' => 0,
        ];
    }
}
