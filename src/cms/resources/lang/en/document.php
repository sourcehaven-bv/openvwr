<?php

declare(strict_types=1);

return [
    'model_singular' => 'Document',
    'model_plural' => 'Documents',
    'table_empty_heading' => 'No documents',
    'attach_processing_records' => 'Link processing activities',

    'name' => 'Name',
    'expires_at' => 'Expiry date',
    'expires_at_required_unless' => 'This field is required if a notification is needed',
    'notify_at' => 'Notification date',
    'location' => 'Where to find it',
    'type' => 'Document type',

    'help_expires_at' => 'The date on which the document must be reassessed.',
    'help_notify_at' => 'On this date the Privacy Officers receive a reminder by email; nothing is sent for a date in the past.',
    'help_type' => 'The kind of document, for example a processor agreement, DPIA or security policy.',
    'help_location' => 'Where the original document can be found, for example a DMS reference or network location.',
    'help_processing_records' => 'The processing activities this document relates to.',

    'notification_options' => [
        'none' => 'none',
        'expires_at' => 'On the expiry date',
        '1_month_before' => '1 month before the expiry date',
        '3_months_before' => '3 months before the expiry date',
        'custom' => 'other, namely',
    ],

    'mail_notification_subject' => 'Document notification',
    'mail_notification_text' => 'Notification regarding document',
    'mail_notification_button_text' => 'View document',
];
