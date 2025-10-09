<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Snapshot;
use App\Models\SnapshotData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SnapshotData>
 */
class SnapshotDataFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'snapshot_id' => Snapshot::factory(),

            'private_markdown' => $this->faker->optional()->markdown(),
            'public_frontmatter' => $this->faker->frontmatter(),
            'public_markdown' => $this->faker->optional()->markdown(),
        ];
    }

    public function forPublicWebsite(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'public_frontmatter' => $this->faker->publicFrontmatter(),
                'public_markdown' => $this->faker->markdown(),
            ];
        });
    }
}
