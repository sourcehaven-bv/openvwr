<?php

declare(strict_types=1);

namespace Database\Factories\Dpia;

use App\Enums\Dpia\PersonalDataType;
use App\Models\Dpia\DpiaPersonalData;
use App\Models\Dpia\DpiaRecord;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DpiaPersonalData>
 */
class DpiaPersonalDataFactory extends Factory
{
    protected $model = DpiaPersonalData::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'dpia_record_id' => DpiaRecord::factory(),

            'description' => $this->faker->words(3, true),
            // Ordinary by default so a factory-made DPIA does not accidentally
            // trigger the paragraaf 12 obligations; tests opt in explicitly.
            'type' => PersonalDataType::ORDINARY,
            'data_subject_category' => $this->faker->word(),
            'source' => $this->faker->word(),
            'order_column' => 0,
        ];
    }

    public function special(): self
    {
        return $this->state(['type' => PersonalDataType::SPECIAL]);
    }
}
