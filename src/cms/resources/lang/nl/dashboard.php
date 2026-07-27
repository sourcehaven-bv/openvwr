<?php

declare(strict_types=1);

return [
    'title' => 'Overzicht',

    'attention' => [
        'heading' => 'Vereist uw aandacht',
        'review_overdue' => 'Reviews verlopen',
        'review_soon' => 'Reviews binnenkort',
        'document_expired' => 'Documenten verlopen',
        'document_soon' => 'Documenten verlopen binnenkort',
        'unsigned_approvals' => 'Te ondertekenen',
        'open_data_breaches' => 'Open datalekken',
    ],

    'filter' => [
        'overdue' => 'Verlopen',
        'soon' => 'Verloopt binnenkort',
        'open_data_breach' => 'Nog in behandeling',
    ],

    'show_all' => 'Toon alle',

    'all_clear' => [
        'heading' => 'Niets vereist op dit moment uw aandacht',
        'description' => 'Er zijn geen verlopen reviews, verlopen documenten, openstaande meldingen of te ondertekenen versies.',
    ],

    'overdue' => [
        'heading' => 'Verlopen',
        'description' => 'Verwerkingen waarvan de periodieke review is verstreken en documenten die zijn vervallen.',
    ],

    /**
     * Short type labels for the lists. The resources' own model_singular values
     * ("Verwerking AVG verantwoordelijke") title a whole page and are too long
     * to sit under every row; these say the same thing at a glance. Kept here
     * rather than shortening model_singular, which the navigation and page
     * headings depend on.
     */
    'type' => [
        'avg_responsible' => 'AVG verantwoordelijke',
        'avg_processor' => 'AVG verwerker',
        'wpg' => 'WPG verantwoordelijke',
    ],

    'approvals' => [
        'heading' => 'Te ondertekenen',
        'description' => 'Versies die op uw handtekening wachten.',
    ],

    'breach' => [
        'heading' => 'Datalekken in behandeling',
        'description' => 'Datalekken die nog niet zijn afgerond. Of een melding bij de Autoriteit '
            . 'Persoonsgegevens nodig is, hangt af van het risico voor de betrokkenen.',
        'discovered_unknown' => 'Ontdekkingsdatum onbekend',
        'no_discovery_date' => 'Vul ontdekkingsdatum in',
        'open_for' => ':duration open',
        'ap_decision_open' => 'Meldplicht nog te beoordelen',
        'in_progress' => 'In behandeling',
    ],
];
