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
    'data_collection_source_help' => 'Binnen het ministerie zijn vele verwerkingen met persoonsgegevens aanwezig. Enerzijds hebben deze betrekking op de specifieke taken van het ministerie. Dit zijn de verwerkingen betreffende de taken en werkzaamheden van het ministerie op haar specifieke beleidsterreinen (de primaire processen op concernniveau). Deze verwerkingen worden ook wel primaire verwerkingen genoemd. Daarnaast zijn er de verwerkingen op het gebied van de bedrijfsvoering (de secundaire processen op concernniveau). Dit worden ook wel secundaire verwerkingen genoemd. Secundaire verwerkingen zijn in de regel verwerkingen die vaker voorkomen, en min of meer een standaardkarakter hebben. Het onderscheid primair/secundair kan van belang zijn voor de wijze waarop de informatie over de verwerking wordt gepubliceerd. Wanneer secundaire verwerkingen steeds betrekking hebben op verwerking van gegevens van het personeel, kan in het publicatiebeleid ervoor zijn gekozen om de informatie niet op de extern toegankelijke website te plaatsen, maar op de interne website, zoals het Rijksportaal.',
    'attention' => 'Let op',

    'all' => 'Alle',
    'add' => 'Toevoegen',
    'and' => 'en',
    'cancel' => 'Annuleren',
    'close' => 'Sluiten',
    'delete' => 'Verwijderen',
    'deleted' => 'Verwijderd',
    'force_delete' => 'Definitief verwijderen',
    'force_deleted' => 'Definitief verwijderd',
    'force_delete_confirm_title' => 'Definitief verwijderen?',
    'force_delete_confirm_description' => 'Dit verwijdert het item en de bijbehorende bestanden onherstelbaar. Deze actie kan niet ongedaan worden gemaakt.',
    'force_delete_confirm_submit' => 'Ja, definitief verwijderen',
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
    'country_options' => [
        'Andorra',
        'Argentinië',
        'Canada (alleen commerciële bedrijven)',
        'Faeröer Eilanden',
        'Guernsey',
        'Isle of Man',
        'Israël',
        'Japan',
        'Jersey',
        'Nieuw-Zeeland',
        'Uruguay',
        'Verenigd Koninkrijk',
        'Verenigde Staten (organisaties die meedoen aan het Data Privacy Framework)',
        'Zwitserland',
        'Zuid-Korea',
    ],
];
