<?php

declare(strict_types=1);

return [
    'model_singular' => 'Processor',
    'model_plural' => 'Processors',
    'table_empty_heading' => 'No processors',

    'name' => 'Name of the organisation',
    'email' => 'Email',
    'phone' => 'Telephone number',

    'measures' => 'Measures',
    'measures_implemented' => 'Established security policy that has also been implemented',

    'other_measures' => 'Other measures',
    'outside_eu_protection_level_description' => 'Explanation of the protection',
    'measures_description' => 'Explanation of the measures',
    'security_access_options' => [
        'Staff working under the authority of the controller',
        'Staff working under the authority of the processor',
        'Third parties',
    ],
    'security_options' => [
        'No',
        'Yes, via a private network',
        'Yes, via a public network',
    ],
];
