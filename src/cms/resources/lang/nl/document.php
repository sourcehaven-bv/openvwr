<?php

declare(strict_types=1);

return [
    'model_singular' => 'Document',
    'model_plural' => 'Documenten',
    'table_empty_heading' => 'Geen documtenten',
    'attach_processing_records' => 'Verwerkingen koppelen',

    'name' => 'Naam',
    'expires_at' => 'Vervaldatum',
    'expires_at_required_unless' => 'Dit veld is verplicht als er notificatie nodig is',
    'notify_at' => 'Notificatie datum',
    'location' => 'Waar te vinden',
    'type' => 'Type document',

    'help_expires_at' => 'De datum waarop het document opnieuw beoordeeld moet worden.',
    'help_notify_at' => 'Op deze datum ontvangen de Privacy Officers een herinnering per e-mail; bij een datum in het verleden wordt niets verstuurd.',
    'help_type' => 'Het soort document, bijvoorbeeld een verwerkersovereenkomst, DPIA of beveiligingsbeleid.',
    'help_location' => 'Waar het originele document te vinden is, bijvoorbeeld een DMS-verwijzing of netwerklocatie.',
    'help_processing_records' => 'De verwerkingen waarop dit document betrekking heeft.',

    'notification_options' => [
        'none' => 'geen',
        'expires_at' => 'op datum zelf',
        '1_month_before' => '1 maand van tevoren',
        '3_months_before' => '3 maanden van tevoren',
        'custom' => 'anders, namelijk',
    ],

    'mail_notification_subject' => 'Document notificatie',
    'mail_notification_text' => 'Noticatie tbv document',
    'mail_notification_button_text' => 'Document bekijken',
];
