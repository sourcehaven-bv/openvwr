<?php

declare(strict_types=1);

namespace App\Manual;

use App\Enums\Authorization\Role;

use function in_array;

/**
 * Who a task is for.
 *
 * Two lists rather than one: the roles that carry the task out, and the roles
 * that only follow along. A task is never hidden on the basis of a role —
 * knowing that a step exists and who performs it is part of understanding the
 * process — so this only decides the wording around it.
 */
readonly class TaskRoles
{
    /**
     * @param array<Role> $performers roles that can carry out the task themselves
     * @param array<Role> $readers roles that can only follow along
     */
    public function __construct(
        public array $performers,
        public array $readers = [],
    ) {
    }

    /**
     * @param array<Role> $roles the roles the current user holds
     */
    public function capabilityFor(array $roles): TaskCapability
    {
        foreach ($roles as $role) {
            if (in_array($role, $this->performers, true)) {
                return TaskCapability::PERFORM;
            }
        }

        foreach ($roles as $role) {
            if (in_array($role, $this->readers, true)) {
                return TaskCapability::READ;
            }
        }

        return TaskCapability::NONE;
    }

    /**
     * The role that gives the user this task, so the page can name it instead
     * of saying "uw rol".
     *
     * A user can hold several roles at once, of which more than one may cover
     * the task. The first match wins: which of them is named matters less than
     * naming one at all, and any of them is a true answer to "why may I do
     * this?".
     *
     * @param array<Role> $roles the roles the current user holds
     */
    public function decidingRoleFor(array $roles): ?Role
    {
        foreach ($roles as $role) {
            if (in_array($role, $this->performers, true)) {
                return $role;
            }
        }

        foreach ($roles as $role) {
            if (in_array($role, $this->readers, true)) {
                return $role;
            }
        }

        return null;
    }
}
