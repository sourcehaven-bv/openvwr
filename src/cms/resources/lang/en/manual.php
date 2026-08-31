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

    'tasks_heading' => 'What would you like to do?',
    'tasks_intro' => 'Choose the task at hand. Every task is short and links to the reference '
        . 'for the explanation, so a screen is described in exactly one place.',

    'reference_heading' => 'Reference',
    'reference_intro' => 'The full explanation, written once. The tasks above link to it.',

    'overview_heading' => 'All topics',
    'overview_intro' => 'Everything in this manual, in one overview.',

    'search_placeholder' => 'Search tasks and reference…',
    'search_results' => 'Found :tasks tasks and :topics topics.',
    'search_clear' => 'Clear search',
    'search_empty' => 'Nothing found. Try another word.',

    'capability_perform' => 'You can do this',
    'capability_read' => 'Read only',
    'capability_none' => 'Not for your role',
    'step_count' => ':count steps',

    'role_can_perform' => 'Your role allows you to carry out this task yourself.',
    'role_can_read' => 'Your role lets you follow along, but not carry out this task. The steps '
        . 'are here so you know what happens and who does it.',
    'role_cannot' => 'This task does not belong to your role. Who can do it is described in',
    'role_see_roles' => 'Roles and permissions.',

    'see_reference' => 'See reference:',
    'done' => 'Done',

    'available_for' => 'Available for',
    'used_in_tasks' => 'Used in these tasks',
    'used_in_no_tasks' => 'This topic is reference without an associated task: you look it up '
        . 'while doing something else.',
];
