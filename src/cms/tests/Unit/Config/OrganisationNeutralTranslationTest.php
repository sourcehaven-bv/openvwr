<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Tests\TestCase;

use function __;
use function it;
use function sprintf;
use function str_contains;
use function strtolower;
use function uses;

uses(TestCase::class);

// Help texts shown in the forms must describe the concept, not the organisation
// of one specific tenant. The terms below assume a Dutch ministry and made the
// text meaningless for tenants that are not one.
const TENANT_SPECIFIC_TERMS = [
    'ministerie',
    'concernniveau',
    'rijksportaal',
    'rijksonderdelen',
];

const ORGANISATION_NEUTRAL_KEYS = [
    'general.data_collection_source',
    'general.data_collection_source_help',
    'general.data_collection_source_help_short',
];

it('keeps the primary/secondary explanation organisation neutral', function (): void {
    foreach (ORGANISATION_NEUTRAL_KEYS as $key) {
        $translation = strtolower(__($key));

        foreach (TENANT_SPECIFIC_TERMS as $term) {
            $this->assertFalse(
                str_contains($translation, $term),
                sprintf('Translation %s must not assume a specific organisation ("%s").', $key, $term),
            );
        }
    }
});
