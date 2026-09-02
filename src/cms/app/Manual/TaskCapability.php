<?php

declare(strict_types=1);

namespace App\Manual;

/**
 * What the current user can do with a task.
 *
 * A task is never hidden on the basis of a role: knowing that a step exists and
 * who performs it is part of understanding the process. Roles only change the
 * wording around the task.
 */
enum TaskCapability: string
{
    case PERFORM = 'perform';
    case READ = 'read';
    case NONE = 'none';
}
