<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LabelColor;
use App\Models\Organisation;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_id' => Organisation::factory(),
            'name' => $this->faker->word(),
            'color' => $this->faker->randomElement(LabelColor::cases()),
        ];
    }
}
