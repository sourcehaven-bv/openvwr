<?php

declare(strict_types=1);

return [
    'id' => 'ID',
    'created_at' => 'Aangemaakt op',
    'review_at' => 'Periodieke review',
    'review_at_hint' => 'Let op als de verwerking op (datum) live gaat dan moet 2,5 jaar na (datum) voorbereidingen getroffen worden voor de periodieke review.',
    'review_at_help' => 'Datum waarop deze verwerking opnieuw beoordeeld moet worden om te '
        . 'controleren of de gegevens nog kloppen. Standaard 2,5 jaar na livegang; '
        . 'rond die datum verschijnt de verwerking in het overzicht van te reviewen verwerkingen.',
    'updated_at' => 'Bewerkt op',
    'public_from' => 'Publiceer vanaf',
    'public_from_set_now' => 'Publiceer vanaf nu',
    'published_at' => 'Link naar publieke pagina',
    'now' => 'Nu',
    'data_collection_source' => 'Primair / Secundair',
    'data_collection_source_help_short' => 'Primair: hoort bij de eigen kerntaak. '
        . 'Secundair: bedrijfsvoering (zoals HR of ICT), vaak terugkerend en standaard.',
    'data_collection_source_help' => 'Een primaire verwerking hoort bij de kerntaak van uw organisatie: het werk '
        . 'waarvoor de organisatie bestaat. Een secundaire verwerking hoort bij de bedrijfsvoering die dat werk '
        . 'ondersteunt, zoals HR, financiën, ICT en facilitaire zaken. Secundaire verwerkingen komen vaker voor '
        . 'en hebben meestal een standaardkarakter. Het onderscheid kan meespelen bij de vraag waar u de '
        . 'verwerking publiceert; volg daarin het publicatiebeleid van uw organisatie.',
    'attention' => 'Let op',

    'all' => 'Alle',
    'add' => 'Toevoegen',
    'and' => 'en',
    'cancel' => 'Annuleren',
    'close' => 'Sluiten',
    'delete' => 'Verwijderen',
    'deleted' => 'Verwijderd',
    'disabled' => 'Uitgeschakeld',
    'download' => 'Downloaden',
    'enabled' => 'Ingeschakeld',
    'error' => 'Fout',
    'export' => 'Exporteren',
    'fg_remarks' => 'FG Opmerkingen (alleen zichtbaar voor FG\'s)',
    'none_selected' => 'geen',
    'save' => 'Opslaan',
    'saved' => 'Opgeslagen',

    'help_country' => 'De landen buiten de EER waarnaar persoonsgegevens worden doorgegeven.',

    'picker_recent' => 'Recent bewerkt',

    'parent' => 'Hoofdverwerking',
    'parent_hint_icon_text' => 'Indien deze verwerking een subverwerking is van een hoofdverwerking kunt u de hoofdverwerking hier aangeven. Bij de hoofdverwerking zijn alle subverwerkingen te vinden in de tabel "Subverwerkingen".',
    'parent_help' => 'Alleen invullen als deze verwerking onderdeel is van een grotere '
        . 'verwerking. Kies dan de overkoepelende verwerking; deze verschijnt daar '
        . 'in de tabel "Subverwerkingen". Laat leeg voor een zelfstandige verwerking.',
    'child' => 'Subverwerking',
    'children' => 'Subverwerkingen',
    'children_help' => 'De verwerkingen die onder deze verwerking vallen. Alleen '
        . 'zelfstandige verwerkingen kunnen worden gekoppeld; na het ontkoppelen is '
        . 'de verwerking weer zelfstandig.',

    'data_loss_confirm_title' => 'Weet u het zeker?',
    'data_loss_confirm_submit' => 'Ja, gegevens verwijderen',
    'data_loss_confirm_cancel' => 'Annuleren',

    'name' => 'Naam',
    'description' => 'Beschrijving',
    'import_id' => 'Import ID',
    'attachments' => 'Bijlagen',

    'yes' => 'Ja',
    'no' => 'Nee',
    'unknown' => 'Onbekend',

    'create_form_action_label' => 'Opslaan',
    'create_another_form_action_label' => 'Opslaan & nieuwe aanmaken',
    'number_create_failed' => 'Het genereren van een (uniek) nummer is mislukt, probeer het opnieuw',

    'manual' => 'Handleiding',
    'go_to_public_page' => 'Bekijk op de publieke website',
    'edit' => 'Bewerken',
    'onepage_nav_label' => 'Snel naar onderdeel',

    'country' => 'Landen',
    'country_other' => 'Anders, namelijk:',
];
