<?php

declare(strict_types=1);

namespace Tests\Feature\Faker;

use function expect;
use function fake;
use function it;

it('generates publicFrontmatter', function (): void {
    $frontmatter = fake()->publicFrontmatter();

    expect($frontmatter)->toBeArray()
        ->toHaveKeys(['title', 'type', 'record'])
        ->and($frontmatter['type'])->toBe('processing-record')
        ->and($frontmatter['record'])->toBeArray()
        ->and($frontmatter['record'])->toHaveKeys(['title', 'description', 'reference']);
});
