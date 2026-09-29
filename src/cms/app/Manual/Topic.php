<?php

declare(strict_types=1);

namespace App\Manual;

use App\Enums\Authorization\Role;

/**
 * One reference topic: the canonical explanation of a single subject.
 *
 * The body is written once, here, in markdown. Tasks link to a topic rather
 * than restating it, which is what keeps the manual a single source of truth.
 */
readonly class Topic
{
    /**
     * @param non-empty-string $id anchor, unique across the manual
     * @param array<Role> $roles roles this topic is relevant to; empty means everyone
     */
    public function __construct(
        public string $id,
        public string $title,
        public string $body,
        public array $roles = [],
        public ?FeatureGate $gate = null,
        public ?string $availability = null,
    ) {
    }

    public function isVisible(): bool
    {
        return $this->gate === null || $this->gate->enabled();
    }

    /**
     * @param ?callable(string): ?string $topicUrl resolves a topic id to its
     *        url, so a cross reference written as `#topic-id` lands on that
     *        topic's page. Without it the anchors are left untouched.
     */
    public function html(?callable $topicUrl = null): string
    {
        return ManualMarkdown::render($this->body, $topicUrl);
    }

    /**
     * The plain text used for searching: title plus body, without markup.
     */
    public function searchText(): string
    {
        return $this->title . ' ' . $this->body;
    }
}
