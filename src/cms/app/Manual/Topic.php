<?php

declare(strict_types=1);

namespace App\Manual;

use App\Enums\Authorization\Role;

use function array_filter;
use function array_map;
use function implode;
use function is_string;

/**
 * One reference topic: the canonical explanation of a single subject.
 *
 * The body is written once, here, in markdown. Tasks link to a topic rather
 * than restating it, which is what keeps the manual a single source of truth.
 *
 * A gate on the topic hides it as a whole. A gate on a fragment of the body
 * hides only that fragment, for text that adds to a topic that is otherwise
 * always true.
 */
readonly class Topic
{
    /** @var array<Fragment> */
    public array $fragments;

    /**
     * @param non-empty-string $id anchor, unique across the manual
     * @param string|array<string|Fragment> $body markdown, or a list of
     *        fragments that are joined as separate paragraphs
     * @param array<Role> $roles roles this topic is relevant to; empty means everyone
     */
    public function __construct(
        public string $id,
        public string $title,
        string|array $body,
        public array $roles = [],
        public ?FeatureGate $gate = null,
        public ?string $availability = null,
    ) {
        $this->fragments = array_map(
            static fn (string|Fragment $part): Fragment => is_string($part) ? new Fragment($part) : $part,
            is_string($body) ? [$body] : $body,
        );
    }

    public function isVisible(): bool
    {
        return $this->gate === null || $this->gate->enabled();
    }

    /**
     * The markdown of the fragments whose feature flag is on.
     */
    public function body(): string
    {
        $visible = array_filter(
            $this->fragments,
            static fn (Fragment $fragment): bool => $fragment->isVisible(),
        );

        return implode("\n\n", array_map(
            static fn (Fragment $fragment): string => $fragment->markdown,
            $visible,
        ));
    }

    /**
     * @param ?callable(string): ?string $topicUrl resolves a topic id to its
     *        url, so a cross reference written as `#topic-id` lands on that
     *        topic's page. Without it the anchors are left untouched.
     */
    public function html(?callable $topicUrl = null): string
    {
        return ManualMarkdown::render($this->body(), $topicUrl);
    }

    /**
     * The plain text used for searching: title plus the visible body.
     */
    public function searchText(): string
    {
        return $this->title . ' ' . $this->body();
    }
}
