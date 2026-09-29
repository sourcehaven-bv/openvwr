<?php

/**
 * The chrome around the manual. The manual text itself is Dutch only: it
 * describes a Dutch privacy register for Dutch organisations, and a half
 * translated manual would be worse than an untranslated one.
 */

declare(strict_types=1);

return [
    'tasks' => 'Tasks',
    'reference' => 'Reference',

    'nav_label' => 'Manual contents',
    'exit' => 'Back to OpenVWR',

    'tasks_heading' => 'What would you like to do?',
    'tasks_intro' => 'Choose the task at hand. Every task is short and links to the reference '
        . 'for the explanation, so a screen is described in exactly one place.',

    'reference_heading' => 'Reference',
    'reference_intro' => 'The full explanation, written once. The tasks above link to it.',

    'search_placeholder' => 'Search tasks and reference…',
    'search_results' => 'Found :tasks tasks and :topics topics.',
    'search_clear' => 'Clear search',
    'search_empty' => 'Nothing found. Try another word.',

    'capability_read' => 'Read only',
    'capability_none' => 'Not for your role',

    'role_can_perform' => 'As :role you can carry out this task yourself.',
    'role_can_read' => 'As :role you can follow along, but not carry out this task. The steps '
        . 'are here so you know what happens and who does it.',
    'role_cannot' => 'This task does not belong to your role. Who can do it is described in',
    'role_see_roles' => 'Roles and permissions.',

    'see_reference' => 'See reference:',
    'done' => 'Done',

    'available_for' => 'Available for',
    'used_in_tasks' => 'Used in these tasks',
];
