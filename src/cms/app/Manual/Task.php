<?php

declare(strict_types=1);

namespace App\Manual;

use App\Enums\Authorization\Role;

use function array_filter;
use function array_map;
use function array_values;
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
     * The steps whose feature flag is on. Everything that shows or searches a
     * task goes through this, never through $steps directly.
     *
     * @return array<Step>
     */
    public function visibleSteps(): array
    {
        return array_values(array_filter(
            $this->steps,
            static fn (Step $step): bool => $step->isVisible(),
        ));
    }

    /**
     * The topics the visible steps link to.
     *
     * @return array<non-empty-string>
     */
    public function topicIds(): array
    {
        $ids = [];

        foreach ($this->visibleSteps() as $step) {
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
            $this->visibleSteps(),
        );

        return $this->title . ' ' . $this->summary . ' ' . $this->intro . ' ' . implode(' ', $steps);
    }
}
