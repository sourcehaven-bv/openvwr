<?php

declare(strict_types=1);

namespace Database\Factories\Dpia;

use App\Enums\EntityNumberType;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\EntityNumber;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DpiaPrescanRecord>
 */
class DpiaPrescanRecordFactory extends Factory
{
    protected $model = DpiaPrescanRecord::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'entity_number_id' => EntityNumber::factory(state: ['type' => EntityNumberType::REGISTER]),

            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),

            // A blank slate by default: no criteria selected, so the outcome is
            // "niet verplicht" until a test opts into one of the triggers.
            'new_legislation' => false,
            'departmental_policy' => false,
            'public_cloud' => false,
            'ap_criteria' => [],
            'edpb_criteria' => [],
            'international_transfer' => false,
            'outside_eea' => false,
            'digital_service' => false,
            'minors' => false,
            'algorithm' => false,
            'high_risk_ai' => false,
        ];
    }
}
