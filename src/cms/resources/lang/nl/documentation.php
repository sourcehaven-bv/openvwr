<?php

declare(strict_types=1);

// Toelichtingen bij formuliersecties, uitsluitend voor het gegenereerde
// datamodel-document (`php artisan docs:datamodel`).
//
// Dit is een ander soort tekst dan information_blocks.php: die legt aan een
// invuller uit hoe hij een veld moet invullen, terwijl deze notities aan een
// lezer buiten de organisatie uitleggen wat het systeem kan vastleggen.
//
// De sleutels worden aangehaald via #[DocNote] bij de schema-methode.

return [
    // De woordenlijst van het document zelf: kolomkoppen en de omschrijving van
    // elk soort invoerveld.
    'column_field' => 'Veld',
    'column_kind' => 'Soort invoer',
    'column_help' => 'Toelichting',

    'kind' => [
        'text' => 'Tekst',
        'textarea' => 'Toelichting',
        'boolean' => 'Ja/nee',
        'date' => 'Datum',
        'choice' => 'Keuze',
        'multiple_choice' => 'Meerkeuze',
        'free_tags' => 'Meerkeuze (vrij)',
        'relation' => 'Koppeling',
        'list' => 'Lijst',
        'file' => 'Bestand',
    ],

    'generated_header' => [
        'line_1' => 'Dit bestand wordt gegenereerd door `just docs-datamodel`.',
        'line_2' => 'Wijzigingen hier gaan verloren.',
        'line_3' => 'De veldtabellen komen uit de Filament-formulieren; pas die aan',
        'line_4' => '(labels en hulpteksten staan in resources/lang/). De omringende',
        'line_5' => 'tekst staat in docs/prose/.',
    ],

    'options_prefix' => 'Keuze uit: :options.',

    'avg_responsible_processing_record' => [
        'stakeholders' => 'Het meest gedetailleerde onderdeel van de registratie. Per categorie '
            . 'betrokkenen wordt vastgelegd welke gewone, bijzondere en gevoelige gegevens '
            . 'worden verwerkt, en per gegeven het verzameldoel, de bewaartermijn en de bron.',
    ],
];
