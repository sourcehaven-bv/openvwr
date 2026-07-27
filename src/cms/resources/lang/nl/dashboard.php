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
        'heading' => 'Geen openstaande acties binnen dit verwerkingsregister',
        'description' => 'Er zijn geen verlopen reviews, verlopen documenten, openstaande meldingen, '
            . 'te ondertekenen versies of versies die op vaststelling wachten.',

        /**
         * For someone without register permissions — the functioneel beheerder.
         * Deliberately says nothing about the register being clean: they cannot
         * see it, so that would be a claim they have no way to check.
         */
        'no_register' => [
            'heading' => 'Geen openstaande acties',
            'description' => 'Uw rol heeft geen taken in het verwerkingsregister. '
                . 'Gebruik het menu om organisaties en gebruikers te beheren.',
        ],
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

    'awaiting_establishment' => [
        'heading' => 'Vast te stellen',
        'description' => 'Versies die ter goedkeuring zijn aangeboden en volledig zijn ondertekend. '
            . 'U kunt ze beoordelen en vaststellen.',
        'signed' => 'Volledig ondertekend',
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
