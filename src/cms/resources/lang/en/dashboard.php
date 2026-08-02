<?php

declare(strict_types=1);

return [
    'title' => 'Overview',

    'attention' => [
        'heading' => 'Requires your attention',
        'review_overdue' => 'Reviews overdue',
        'review_soon' => 'Reviews due soon',
        'document_expired' => 'Documents expired',
        'document_soon' => 'Documents expiring soon',
        'unsigned_approvals' => 'Awaiting signature',
        'open_data_breaches' => 'Open personal data breaches',
    ],

    'filter' => [
        'overdue' => 'Overdue',
        'soon' => 'Expires soon',
        'open_data_breach' => 'Still being handled',
    ],

    'show_all' => 'Show all',

    'all_clear' => [
        'heading' => 'No outstanding actions within this record of processing activities',
        'description' => 'There are no overdue reviews, expired documents, outstanding reports, '
            . 'versions awaiting signature or versions awaiting establishment.',

        /**
         * For someone without register permissions — the functional administrator.
         * Deliberately says nothing about the register being clean: they cannot
         * see it, so that would be a claim they have no way to check.
         */
        'no_register' => [
            'heading' => 'No outstanding actions',
            'description' => 'Your role has no tasks in the record of processing activities. '
                . 'Use the menu to manage organisations and users.',
        ],
    ],

    'overdue' => [
        'heading' => 'Overdue',
        'description' => 'Processing activities whose periodic review has lapsed and documents that have expired.',
    ],

    /**
     * Short type labels for the lists. The resources' own model_singular values
     * ("GDPR controller processing activity") title a whole page and are too long
     * to sit under every row; these say the same thing at a glance. Kept here
     * rather than shortening model_singular, which the navigation and page
     * headings depend on.
     */
    'type' => [
        'avg_responsible' => 'GDPR controller',
        'avg_processor' => 'GDPR processor',
        'wpg' => 'Wpg controller',
    ],

    'approvals' => [
        'heading' => 'Awaiting signature',
        'description' => 'Versions awaiting your signature.',
    ],

    'awaiting_establishment' => [
        'heading' => 'Awaiting establishment',
        'description' => 'Versions that have been submitted for approval and are fully signed. '
            . 'You can assess and establish them.',
        'signed' => 'Fully signed',
    ],

    'breach' => [
        'heading' => 'Personal data breaches being handled',
        'description' => 'Personal data breaches that have not yet been completed. Whether a notification to the Dutch Data '
            . 'Protection Authority (Autoriteit Persoonsgegevens) is required depends on the risk to the data subjects.',
        'discovered_unknown' => 'Discovery date unknown',
        'no_discovery_date' => 'Enter discovery date',
        'open_for' => 'open for :duration',
        'ap_decision_open' => 'Notification obligation still to be assessed',
        'in_progress' => 'Being handled',
    ],
];
