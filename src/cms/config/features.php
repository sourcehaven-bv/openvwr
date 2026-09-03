<?php

declare(strict_types=1);

// Optional features that a tenant can switch off. Every flag defaults to true,
// so an existing deployment keeps the behaviour it already has. Underlying data
// and exports keep working when a feature is switched off.
return [
    // publishing records to the public static website
    'publishing' => env('FEATURE_PUBLISHING', true),

    // the WPG register ("WPG verantwoordelijke") and its lookup list
    'wpg' => env('FEATURE_WPG', true),
];
