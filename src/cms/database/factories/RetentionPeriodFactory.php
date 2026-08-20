<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organisation;
use App\Models\RetentionPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RetentionPeriod>
 */
class RetentionPeriodFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),

            'name' => $this->faker->sentence(),
            'enabled' => true,
        ];
    }
}
