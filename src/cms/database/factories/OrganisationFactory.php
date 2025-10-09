<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Config\Config;
use App\Enums\Media\MediaGroup;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\EntityNumberCounter;
use App\Models\Organisation;
use App\Models\ResponsibleLegalEntity;
use App\Models\User;
use App\Models\Wpg\WpgProcessingRecord;
use App\Vendor\MediaLibrary\Media;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Webmozart\Assert\Assert;

use function assert;
use function database_path;
use function file_exists;
use function file_get_contents;
use function is_string;
use function sprintf;

/**
 * @extends Factory<Organisation>
 */
class OrganisationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $allowedEmailDomains = [
            $this->faker->domainName(),
            $this->faker->domainName(),
            $this->faker->domainName(),
        ];

        return [
            'name' => $this->faker->company(),
            'slug' => $this->faker->unique()->slug(),
            'allowed_ips' => '*.*.*.*',
            'responsible_legal_entity_id' => ResponsibleLegalEntity::factory(),
            'review_at_default_in_months' => $this->faker->numberBetween(0, 60),
            'public_website_content' => $this->faker->optional()->sentence(),
            'register_entity_number_counter_id' => EntityNumberCounter::factory(),
            'databreach_entity_number_counter_id' => EntityNumberCounter::factory(),
            'public_from' => $this->faker->optional()->anyDate(),
            'allowed_email_domains' => $this->faker->randomElements($allowedEmailDomains, $this->faker->numberBetween(0, 3)),
        ];
    }

    public function withAllRelatedEntities(): self
    {
        return self::withUsers()
            ->withAlgorithmRecords()
            ->withAvgProcessorProcessingRecords()
            ->withAvgResponsibleProcessingRecords()
            ->withDataBreachRecords()
            ->withDocuments()
            ->withPosterImage()
            ->withWpgProcessingRecords();
    }

    public function withPublishedEntities(): self
    {
        return self::withUsers()
            ->withAlgorithmRecords()
            ->withAvgProcessorProcessingRecords()
            ->withAvgResponsibleProcessingRecordsPublished()
            ->withDataBreachRecords()
            ->withDocuments()
            ->withPosterImage()
            ->withWpgProcessingRecords();
    }

    public function withAlgorithmRecords(?int $count = null): self
    {
        return $this->afterCreating(function (Organisation $organisation) use ($count): void {
            AlgorithmRecord::factory()
                ->for($organisation)
                ->recycle($organisation)
                ->withAllRelatedEntities()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withAvgProcessorProcessingRecords(?int $count = null): self
    {
        return $this->afterCreating(function (Organisation $organisation) use ($count): void {
            AvgProcessorProcessingRecord::factory()
                ->for($organisation)
                ->withAllRelatedEntities()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->recycle($organisation)
                ->create();
        });
    }

    public function withAvgResponsibleProcessingRecords(?int $count = null): self
    {
        return $this->afterCreating(function (Organisation $organisation) use ($count): void {
            AvgResponsibleProcessingRecord::factory()
                ->for($organisation)
                ->recycle($organisation)
                ->withAllRelatedEntities()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withAvgResponsibleProcessingRecordsPublished(?int $count = null): self
    {
        return $this->afterCreating(function (Organisation $organisation) use ($count): void {
            AvgResponsibleProcessingRecord::factory()
                ->for($organisation)
                ->recycle($organisation)
                ->withAvgGoals()
                ->withContactPersons()
                ->withDataBreachRecords()
                ->withDocuments()
                ->withFgRemark()
                ->withProcessors()
                ->withReceivers()
                ->withRemarks()
                ->withResponsibles()
                ->withStakeholders()
                ->withSystems()
                ->withTags()
                ->withPublishedSnapshot()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create([
                    'public_from' => now()->subDay(),
                ]);
        });
    }

    public function withDataBreachRecords(?int $count = null): self
    {
        return $this->afterCreating(function (Organisation $organisation) use ($count): void {
            DataBreachRecord::factory()
                ->withAllRelatedEntities()
                ->for($organisation)
                ->recycle($organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withDocuments(?int $count = null): self
    {
        return $this->afterCreating(function (Organisation $organisation) use ($count): void {
            Document::factory()
                ->for($organisation)
                ->recycle($organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withPosterImage(?Filesystem $filesystem = null): self
    {
        if ($filesystem === null) {
            $filesystem = Storage::disk(Config::string('media-library.filesystem_disk'));
        }

        return $this->afterCreating(static function (Organisation $organisation) use ($filesystem): void {
            $imagePath = database_path(sprintf('fixtures/%s_organisation_poster.jpeg', $organisation->slug));

            if (!file_exists($imagePath)) {
                $imagePath = database_path('fixtures/organisation_poster.jpeg');
            }

            Assert::fileExists($imagePath);

            /** @var Media $posterImage */
            $posterImage = Media::factory()
                ->for($organisation)
                ->create([
                    'model_id' => $organisation->id,
                    'model_type' => Organisation::class,
                    'file_name' => 'poster.jpeg',
                    'mime_type' => 'image/jpeg',
                    'collection_name' => MediaGroup::ORGANISATION_POSTERS->value,
                ]);

            $imageContents = file_get_contents($imagePath);
            assert(is_string($imageContents));
            $storagePath = $posterImage->getPathRelativeToRoot();

            $filesystem->put($storagePath, $imageContents);
            $organisation->media()->save($posterImage);
        });
    }

    public function withUsers(?int $count = null): self
    {
        return $this->afterCreating(function (Organisation $organisation) use ($count): void {
            User::factory()
                ->hasAttached($organisation)
                ->recycle($organisation)
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }

    public function withWpgProcessingRecords(?int $count = null): self
    {
        return $this->afterCreating(function (Organisation $organisation) use ($count): void {
            WpgProcessingRecord::factory()
                ->for($organisation)
                ->recycle($organisation)
                ->withAllRelatedEntities()
                ->count($count ?? $this->faker->randomDigitNotNull())
                ->create();
        });
    }
}
