<?php

declare(strict_types=1);

namespace App\Manual;

use App\Enums\Authorization\Role;

use function array_map;
use function implode;

/**
 * One task: something a user wants to get done, in a few steps.
 *
 * Tasks are the entry point of the manual. They are deliberately short and
 * link into the reference layer for every explanation, so a screen is
 * described in exactly one place.
 */
readonly class Task
{
    /**
     * @param non-empty-string $id
     * @param array<Step> $steps
     */
    public function __construct(
        public string $id,
        public string $group,
        public string $title,
        public string $summary,
        public string $intro,
        public array $steps,
        public TaskRoles $roles,
        public ?FeatureGate $gate = null,
        public ?string $done = null,
    ) {
    }

    public function isVisible(): bool
    {
        return $this->gate === null || $this->gate->enabled();
    }

    /**
     * Whether the given roles can carry out this task, can only read along, or
     * have nothing to do with it.
     *
     * @param array<Role> $roles
     */
    public function capabilityFor(array $roles): TaskCapability
    {
        return $this->roles->capabilityFor($roles);
    }

    /**
     * @return array<non-empty-string>
     */
    public function topicIds(): array
    {
        $ids = [];

        foreach ($this->steps as $step) {
            foreach ($step->topicIds as $topicId) {
                $ids[] = $topicId;
            }
        }

        return $ids;
    }

    public function searchText(): string
    {
        $steps = array_map(
            static fn (Step $step): string => $step->title . ' ' . $step->body,
            $this->steps,
        );

        return $this->title . ' ' . $this->summary . ' ' . $this->intro . ' ' . implode(' ', $steps);
    }
}
