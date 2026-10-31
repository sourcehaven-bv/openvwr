<?php

declare(strict_types=1);

namespace App\Manual;

/**
 * One paragraph, or a run of paragraphs, of a topic body.
 *
 * A topic body is usually a single fragment without a gate. Split it into
 * fragments when part of the text is only true while a feature flag is on: the
 * gated fragment then drops out of the rendered page and the search text, and
 * the rest of the topic stays.
 */
readonly class Fragment
{
    public function __construct(
        public string $markdown,
        public ?FeatureGate $gate = null,
    ) {
    }

    public function isVisible(): bool
    {
        return $this->gate === null || $this->gate->enabled();
    }
}
